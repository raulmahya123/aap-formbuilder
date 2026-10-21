import fs from 'node:fs';
import path from 'node:path';
const walk=d=>fs.readdirSync(d,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(d,e.name)):[path.join(d,e.name)]);
const voids=new Set(['input','img','br','hr','meta','link','source','wbr']);
function parse(s){const nodes=[],stack=[],re=/<(\/?)([\w.:-]+)\b((?:[^>"']|"[^"]*"|'[^']*')*)>/g;let m;while((m=re.exec(s))){if(m[1]){let i=stack.findLastIndex(n=>n.tag===m[2]);if(i<0)continue;let n=stack[i];n.close=m.index;n.end=re.lastIndex;stack.length=i;}else{let n={tag:m[2],start:m.index,openEnd:re.lastIndex,attrs:m[3],parent:stack.at(-1)};nodes.push(n);if(!voids.has(n.tag)&&!m[0].endsWith('/>'))stack.push(n);else n.end=re.lastIndex;}}return nodes;}
function ancestor(n,tag){for(let p=n.parent;p;p=p.parent)if(p.tag===tag)return p;}
function actionFor(attrs,body){
 const text=body.replace(/<svg[\s\S]*?<\/svg>|<[^>]*>/g,'').trim();
 const hints=attrs+' '+text;
 if(/password|kata sandi/i.test(hints))return 'reset-password';
 if(/nonaktif|deactivat|\bban\b/i.test(hints))return 'deactivate';
 if(/delete|destroy|hapus|trash/i.test(hints))return 'delete';
 if(/members|anggota/i.test(hints))return 'members';
 if(/download|unduh|\bzip\b|unduh_pdf/i.test(hints))return 'download';
 if(/upload|unggah/i.test(hints))return 'upload';
 if(/duplicat|duplikat|copy|salin/i.test(hints))return 'duplicate';
 if(/history|riwayat/i.test(hints))return 'history';
 if(/approve|setujui|setuju/i.test(hints))return 'approve';
 if(/activat|aktif|resolve|selesai/i.test(hints))return 'activate';
 if(/assign|acl|akses/i.test(hints))return 'assign';
 if(/\.edit|edit|ubah|pencil/i.test(hints))return 'edit';
 return 'view';
}
function convert(content){
 return content.replace(/<(x-ui\.button|x-ui\.link|a|button)\b((?:[^>"']|"[^"]*"|'[^']*')*)>([\s\S]*?)<\/\1>/g,(all,tag,attrs,body)=>{
   if(attrs.includes('data-reset-url'))return all;
   const action=actionFor(attrs,body);
   const label=attrs.match(/(?:aria-label|title)="([^"]*)"/)?.[1];
   attrs=attrs.replace(/\s+(?:class|variant|title|aria-label)="[^"]*"/g,'');
   if((tag==='button'||tag==='x-ui.button')&&!/\btype=/.test(attrs))attrs+=' type="submit"';
   const labels={view:'Lihat detail',edit:'Edit',delete:'Hapus',members:'Kelola anggota',activate:'Aktifkan',deactivate:'Nonaktifkan',download:'Unduh',upload:'Unggah','reset-password':'Reset password',more:'Aksi lainnya',duplicate:'Duplikat',assign:'Atur akses',history:'Riwayat',approve:'Setujui'};
   return `<x-ui.icon-button action="${action}" label="${label??labels[action]}"${attrs} />`;
 }).replace(/<span\b[^>]*>\s*\|\s*<\/span>/g,'')
 .replace(/\b(?:class="[^"\n]*?)(?:flex-wrap)([^"\n]*")/g,m=>m.replace('flex-wrap','flex-nowrap'));
}
let cells=0,files=0,unknown=[];
for(const f of walk('resources/views')){
 if(!f.endsWith('.blade.php')||/(?:components|pdf|emails)[\\/]/.test(f)||f.endsWith('pdf_data.blade.php'))continue;
 let s=fs.readFileSync(f,'utf8'),ns=parse(s),edits=[];
 for(const table of ns.filter(n=>n.tag==='x-ui.table'&&n.end)){
   const headers=ns.filter(n=>n.tag==='th'&&ancestor(n,'x-ui.table')===table);
   const indexes=headers.map((n,i)=>/^\s*(?:aksi|actions?|tindakan)\s*$/i.test(s.slice(n.openEnd,n.close).replace(/<[^>]*>/g,''))?i:-1).filter(i=>i>=0);
   for(const row of ns.filter(n=>n.tag==='tr'&&ancestor(n,'x-ui.table')===table&&ancestor(n,'tbody'))){
     const tds=ns.filter(n=>n.tag==='td'&&n.parent===row&&n.end);
     for(const [index,td]of tds.entries()){
       if(/colspan/.test(td.attrs))continue;
       const content=s.slice(td.openEnd,td.close);
       if(!indexes.includes(index)&&!(/<(?:x-ui.button|button)\b/.test(content)&&/delete|edit|hapus/i.test(content)))continue;
       if(!/<(?:x-ui\.(?:button|link)|a|button)\b/.test(content))continue;
       const result=convert(content);
       const count=(result.match(/<x-ui.icon-button\b/g)||[]).length;
       const inner=count>4?`<x-ui.action-menu>${result}</x-ui.action-menu>`:result;
       // Remove only presentation wrappers; authorization directives and complete forms stay in place.
       const flat=inner;
       const wrapped=`<x-ui.table-actions>${flat}</x-ui.table-actions>`;
       const attrs=td.attrs.replace(/class="([^"]*)"/,(_,c)=>`class="ui-action-cell ${c.replace(/\bw-(?:\[[^\]]+\]|\d+)/g,'')}"`);
       edits.push([td.start,td.end,`<td${attrs}>${wrapped}</td>`]);cells++;
     }
   }
 }
 for(const[a,b,t]of edits.sort((a,b)=>b[0]-a[0]))s=s.slice(0,a)+t+s.slice(b);
 // Shared delete confirmation retains the original message and native submit semantics.
 s=s.replace(/<form\b(?:[^>"']|"[^"]*"|'[^']*')*>/g,tag=>tag.replace(/onsubmit="return confirm\('([^']*)'\);?"/,(_,msg)=>`data-confirm="${msg}"`));
 // Create controls use the actual Lucide Plus rather than a text glyph.
 s=s.replace(/<x-ui\.(link|button)\b((?:[^>"']|"[^"]*"|'[^']*')*)>([\s\S]*?)<\/x-ui\.\1>/g,(all,tag,a,b)=>{
   if(!/variant="primary"/.test(a)||!/\.create['",)]/.test(a)||a.includes('icon='))return all;
   b=b.replace(/<x-ui-icon\b[^>]*\/>/g,'').replace(/^[\s+＋➕📂📋📝]+/u,'');
   return `<x-ui.${tag} icon="plus"${a}>${b}</x-ui.${tag}>`;
 });
 if(edits.length||s!==fs.readFileSync(f,'utf8')){fs.writeFileSync(f,s);files++;}
}
console.log(`Migrated ${cells} action-cell templates across ${files} views.`);
