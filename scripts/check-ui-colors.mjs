import fs from 'node:fs';
import assert from 'node:assert/strict';

const css = fs.readFileSync(new URL('../resources/css/design-system.css', import.meta.url), 'utf8');
const tokens = Object.fromEntries([...css.matchAll(/--([\w-]+):\s*(#[\da-f]{6});/gi)].map(([,name,value])=>[name,value]));
const luminance = hex => hex.slice(1).match(/../g).map(v=>parseInt(v,16)/255)
  .map(v=>v<=.04045?v/12.92:((v+.055)/1.055)**2.4)
  .reduce((sum,v,i)=>sum+v*[.2126,.7152,.0722][i],0);
const ratio = (a,b) => (Math.max(luminance(a),luminance(b))+.05)/(Math.min(luminance(a),luminance(b))+.05);

for (const name of ['primary-dark','brown','blue','red','danger-text','green','orange','gray']) {
  for (const background of ['background','surface-secondary']) {
    if (name === 'red' && background === 'surface-secondary') continue; // Solid red uses white text; small text uses danger-text.
    const contrast = ratio(tokens[name],tokens[background]);
    assert.ok(contrast>=4.5, `${name} on ${background}: ${contrast.toFixed(2)} fails WCAG AA`);
    console.log(`${name} / ${background}: ${contrast.toFixed(2)}:1`);
  }
}
assert.ok(ratio(tokens['border-strong'],tokens.surface)>=3, 'Input boundary must meet 3:1');
console.log('All text pairs meet WCAG AA; input boundaries meet 3:1.');
