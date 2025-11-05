// src/composables/user.js
import { usePage } from '@inertiajs/vue3';

const page = usePage();

export function useUser() {
  const user = page.props?.auth?.user;

  return { user };
};

// 🔧 FLATTEN + normalize: strings | arrays | nested arrays | comma lists
const normalize = (v) =>
  (Array.isArray(v) ? v : [v])
    .flat(Infinity) // <-- supports role(field, ['admin','guest'])
    .filter(Boolean)
    .flatMap(x =>
      typeof x === 'string'
        ? x.split(',').map(s => s.trim().toLowerCase()).filter(Boolean)
        : [String(x).toLowerCase()]
    );

// pull roles from common shapes
const extractRoles = (subject) => {
  if (!subject) return [];
  const raw = subject.roles ?? subject.role ?? null;

  if (Array.isArray(raw)) {
    // strings or objects like {name/title/slug}
    return normalize(raw.map(r => r?.name ?? r?.title ?? r?.slug ?? r));
  }
  if (raw && typeof raw === 'object') {
    return normalize([raw.name ?? raw.title ?? raw.slug ?? '']);
  }
  return normalize(raw); // string or falsy
};

/** Proxy so you can do `user.id` without `.value`, stays reactive to Inertia page props */
export const user = new Proxy({}, {
  get(_, key) {
    return page.props?.auth?.user?.[key];
  },
  has(_, key) {
    const u = page.props?.auth?.user ?? {};
    return key in u;
  },
  ownKeys() {
    return Reflect.ownKeys(page.props?.auth?.user ?? {});
  },
  getOwnPropertyDescriptor() {
    return { enumerable: true, configurable: true };
  },
});

/**
 * role(...) multi-mode checker
 *
 * 1) Current user:            role('admin','superadmin') or role(['admin','superadmin'])
 * 2) Any object (optional):   role(field, 'guest') or role(field, ['admin','guest'])
 * 3) No args → has any role:  role()
 */
export function role(...args) {
  const first = args[0];
  const isSubject = first && typeof first === 'object' && !Array.isArray(first);

  const subject = isSubject ? first : (page.props?.auth?.user ?? null);
  const wantedRoles = isSubject ? args.slice(1) : args;

  const current = extractRoles(subject);
  const wanted = normalize(wantedRoles);

  if (!wanted.length) return current.length > 0;
  return current.some(r => wanted.includes(r));
}

/** Reusable predicate:
 *   const isGuestOrAdmin = role.checker(['guest','admin']);
 *   rows.filter(isGuestOrAdmin);
 */
role.checker = (...wanted) => (subject) => role(subject, ...wanted);