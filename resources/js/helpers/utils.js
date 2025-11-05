import axios from "axios";

/**
 * PHP-like in_array for JS
 * @param {*} needle   value to search for
 * @param {Array} haystack  array to search in
 * @param {boolean} [strict=false]  if true, use strict (===) comparison like PHP's $strict=true
 * @returns {boolean}
 */
export function inArray(needle, haystack, strict = false) {
  if (!Array.isArray(haystack)) throw new TypeError('haystack must be an array');

  if (strict) {
    // Strict: same as PHP's in_array with $strict = true
    // includes uses SameValueZero (so NaN matches NaN, which is fine for JS)
    return haystack.includes(needle);
  }

  // Loose: mimic PHP's == coercion behavior
  for (const v of haystack) {
    // JS loose equality covers the common PHP-like coercions: '1' == 1, true == 1, '' == 0, etc.
    if (v == needle) return true;
    // Handle NaN ~ NaN in loose mode too
    if (typeof v === 'number' && typeof needle === 'number' && Number.isNaN(v) && Number.isNaN(needle)) {
      return true;
    }
  }
  return false;
}

// null check
// empty: mimic PHP's empty()
export function empty(v) {
  if (v == null) return true;                 // undefined, null
  if (typeof v === 'boolean') return v === false;
  if (typeof v === 'number') return v === 0 || Number.isNaN(v); // PHP has no NaN; treating as empty is practical
  if (typeof v === 'bigint') return v === 0n;
  if (typeof v === 'string') return v === '' || v === '0';
  if (Array.isArray(v)) return v.length === 0;
  if (v instanceof Map || v instanceof Set) return v.size === 0;
  // In PHP, objects are never "empty" by empty()
  return false;
}

// emptyObj: empty plus {} as empty
export function emptyObj(v) {
  if (empty(v)) return true;
  if (v && typeof v === 'object' &&
    !Array.isArray(v) &&
    !(v instanceof Map) && !(v instanceof Set)) {
    return Object.keys(v).length === 0; // {}
  }
  return false;
}

// download
export async function downloadExcel(url, fileName) {
  try {
    const res = await axios.get(route(url, { file_name: fileName }), { responseType: "blob" });
    if (res.data) {
      const url = window.URL.createObjectURL(new Blob([res.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', fileName);
      document.body.appendChild(link);
      link.click();
      link.remove();
    };
  } catch (e) {
    console.error('Export failed:', e);
  }
};