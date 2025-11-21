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
// empty: mimic PHP's empty() but also check object and array
export function empty(v) {
  // null / undefined
  if (v == null) return true;

  // boolean
  if (typeof v === 'boolean') return v === false;

  // number
  if (typeof v === 'number') return v === 0 || Number.isNaN(v);

  // bigint
  if (typeof v === 'bigint') return v === 0n;

  // string
  if (typeof v === 'string') return v === '' || v === '0';

  // array
  if (Array.isArray(v)) return v.length === 0;

  // map / set
  if (v instanceof Map || v instanceof Set) return v.size === 0;

  // object (treat {} as empty)
  if (typeof v === 'object') return Object.keys(v).length === 0;

  // everything else (functions, symbols, etc.) not empty
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

// to capitalize only first letter in a line
export function capitalizeLine(str = '') {
  return str.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
}