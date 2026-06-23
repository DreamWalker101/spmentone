/* content-loader.js — loads content.json and applies editable fields to the page.
 * Elements are targeted via data-pp="path.to.key" attributes.
 * href/src updates use data-pp-href="..." and data-pp-src="..." respectively.
 */
(function () {
  'use strict';

  var CONTENT_PATH = (function () {
    /* Work out the root-relative path to content.json regardless of page depth */
    var depth = window.location.pathname.replace(/\/[^\/]*$/, '').split('/').length - 1;
    return depth > 0 ? '../'.repeat(depth) + 'content.json' : 'content.json';
  }());

  function get(obj, path) {
    return path.split('.').reduce(function (o, k) {
      return (o && o[k] !== undefined) ? o[k] : null;
    }, obj);
  }

  function apply(content) {
    /* Text content */
    document.querySelectorAll('[data-pp]').forEach(function (el) {
      var val = get(content, el.getAttribute('data-pp'));
      if (val !== null && val !== '') {
        el.textContent = val;
      }
    });

    /* href updates (links) */
    document.querySelectorAll('[data-pp-href]').forEach(function (el) {
      var val = get(content, el.getAttribute('data-pp-href'));
      if (val !== null && val !== '') {
        el.href = val;
      }
    });

    /* src updates (images) */
    document.querySelectorAll('[data-pp-src]').forEach(function (el) {
      var val = get(content, el.getAttribute('data-pp-src'));
      if (val !== null && val !== '') {
        el.src = val;
      }
    });
  }

  /* Fetch content.json — served as a static file by the PHP server */
  fetch(CONTENT_PATH)
    .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
    .then(apply)
    .catch(function (e) { console.warn('[content-loader] Could not load content.json:', e); });
}());
