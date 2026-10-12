// Normalises a rendered page so the old PHP site and the Laravel site can be diffed:
//   node tools/parity_norm.js <in.html> <out.txt> [laravel-origin]
// Ignores whitespace, cache-busting ?v=, &amp; vs &, SEO <meta>/<link rel=canonical> that the CMS adds, and the Laravel origin in links.
const fs = require('fs');
const [, , inFile, outFile, origin = 'http://127.0.0.1:8000'] = process.argv;
let h = fs.readFileSync(inFile, 'utf8');
h = h.replace(/\?v=\d+/g, '')
    .replace(/&amp;/g, '&')
    .split(origin + '/').join('').split(origin).join('index.php')
    .replace(/<link rel="canonical"[^>]*>/g, '')
    .replace(/<meta property="og:[^>]*>/g, '')
    .replace(/<meta name="(description|keywords|robots)"[^>]*>/g, '')
    .replace(/<meta name="csrf-token"[^>]*>/g, '')
    .replace(/\s+/g, ' ')
    .replace(/> /g, '>').replace(/ </g, '<');
fs.writeFileSync(outFile, h.replace(/></g, '>\n<'));
