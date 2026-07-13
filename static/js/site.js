/* site.js — canvas grid, typed roles, scrollspy, reveal, count-up, contact form.
   All config comes from data-* attributes / JSON script tag rendered by Hugo. */
(function(){
"use strict";
const rm = matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---- typed roles (list injected by template) ---- */
function initTyped(){
  const el=document.getElementById('typed'); if(!el) return;
  const cfg=document.getElementById('site-cfg');
  const roles=cfg?JSON.parse(cfg.textContent).roles:[];
  if(!roles.length) return;
  const staticLine=el.dataset.static;               // editorial presets: fixed tagline
  if(staticLine){el.textContent=staticLine;return}
  if(rm){el.textContent=roles[+(el.dataset.pin||0)];return}
  let r=+(el.dataset.pin||0),i=0,del=false;
  (function tick(){const w=roles[r];el.textContent=w.slice(0,i);
    if(!del&&i<w.length){i++;setTimeout(tick,55)}
    else if(!del){del=true;setTimeout(tick,1500)}
    else if(i>0){i--;setTimeout(tick,26)}
    else{del=false;r=(r+1)%roles.length;setTimeout(tick,280)}})();
}

/* ---- canvas dot grid (MANDATORY perf pattern, copied verbatim from mockup:
   offscreen prerender -> drawImage composite, 180px glow radius, zero-size
   guards, visibilitychange pause, rAF-coalesced pointermove) ---- */
function initCanvas(){
  const cv=document.getElementById('grid'); if(!cv) return;
  const cx=cv.getContext('2d');
  let W,H,mx=-999,my=-999;const GAP=34,R=180;
  const off=document.createElement('canvas');const ox=off.getContext('2d');
  function renderStatic(){
    if(!W||!H)return;
    const css=getComputedStyle(document.documentElement);
    const base=css.getPropertyValue('--fg-dim').trim();
    off.width=W;off.height=H;ox.clearRect(0,0,W,H);
    ox.fillStyle=base;ox.globalAlpha=0.09;
    for(let x=GAP/2;x<W;x+=GAP)for(let y=GAP/2;y<H;y+=GAP)ox.fillRect(x-.5,y-.5,1,1);
    ox.globalAlpha=1;}
  function draw(){
    if(!W||!H||!off.width)return;
    cx.clearRect(0,0,W,H);cx.drawImage(off,0,0);
    if(mx<0)return;
    const acc=getComputedStyle(document.documentElement).getPropertyValue('--accent').trim();
    const x0=Math.max(GAP/2,Math.floor((mx-R)/GAP)*GAP+GAP/2),x1=Math.min(W,mx+R);
    const y0=Math.max(GAP/2,Math.floor((my-R)/GAP)*GAP+GAP/2),y1=Math.min(H,my+R);
    cx.fillStyle=acc;
    for(let x=x0;x<=x1;x+=GAP)for(let y=y0;y<=y1;y+=GAP){
      const d=Math.hypot(x-mx,y-my);if(d>R)continue;
      const near=1-d/R;cx.globalAlpha=0.09+near*0.5;
      const sz=1+near*1.6;cx.fillRect(x-sz/2,y-sz/2,sz,sz);}
    cx.globalAlpha=1;}
  function size(){W=cv.width=innerWidth;H=cv.height=innerHeight;renderStatic();draw();}
  addEventListener('resize',size);
  document.addEventListener('visibilitychange',()=>{if(document.hidden){mx=my=-999;}});
  if(!rm&&matchMedia('(hover:hover)').matches){
    let raf=null;
    addEventListener('pointermove',e=>{mx=e.clientX;my=e.clientY;if(!raf)raf=requestAnimationFrame(()=>{draw();raf=null;});});
  }
  size();
}

/* ---- scrollspy + crumb + reveal (copy from mockup, unchanged) ---- */
function initSpy(){
  const secs=[...document.querySelectorAll('section.card')];
  const navA=[...document.querySelectorAll('#nav a')];
  const spy=new IntersectionObserver(es=>es.forEach(e=>{
    if(e.isIntersecting){
      e.target.querySelector('.in')?.classList.add('vis');
      const id=e.target.id;
      navA.forEach(a=>{const cur=a.getAttribute('href')==='#'+id;a.classList.toggle('on',cur);cur?a.setAttribute('aria-current','true'):a.removeAttribute('aria-current');});
      document.getElementById('crumb').textContent='~/'+(id==='hero'?'home':id);
    }
  }),{threshold:.45});
  secs.forEach(s=>spy.observe(s));
  document.querySelectorAll('.reveal').forEach(x=>{const o=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('vis');o.disconnect();}}),{threshold:.2});o.observe(x);});
}

/* ---- stat count-up (copy from mockup, unchanged) ---- */
function initCount(){
  const cu=new IntersectionObserver(es=>es.forEach(e=>{
    if(!e.isIntersecting)return;cu.unobserve(e.target);
    if(rm)return;
    const t=e.target,end=parseFloat(t.dataset.count),pre=t.dataset.pre||'',post=t.dataset.post||'';
    const dec=(''+t.dataset.count).includes('.')?2:0;let st=null;
    requestAnimationFrame(function step(ts){if(!st)st=ts;const p=Math.min((ts-st)/900,1);
      t.textContent=pre+(end*(.2+.8*p*p)).toFixed(dec).replace(/\.00$/,'')+post;
      if(p<1)requestAnimationFrame(step);else t.textContent=pre+end.toFixed(dec).replace(/\.00$/,'')+post;});
  }),{threshold:.6});
  document.querySelectorAll('.stat .num[data-count]').forEach(n=>cu.observe(n));
}

/* ---- contact form: fetch POST, inline success/error, double-submit guard.
   Targets the Formspree action directly and creates its own .form-msg output
   node if the template didn't provide one. ---- */
function initForm(){
  const form=document.querySelector('form[action^="https://formspree.io"]');
  if(!form) return;
  const btn=form.querySelector('[type="submit"]');
  let out=form.querySelector('.form-msg');
  if(!out){out=document.createElement('p');out.className='form-msg';out.setAttribute('aria-live','polite');form.appendChild(out);}
  form.addEventListener('submit',function(e){
    e.preventDefault();
    if(btn&&btn.disabled) return;          // already sending — ignore double clicks
    if(btn) btn.disabled=true;
    out.textContent='';out.className='form-msg';
    fetch(form.action,{
      method:'POST',
      body:new FormData(form),
      headers:{Accept:'application/json'}
    }).then(function(res){
      if(res.ok){
        form.reset();
        out.textContent='Thank you for your message. It has been sent.';
        out.classList.add('ok');
      } else {
        return res.json().then(function(body){
          out.textContent=(body&&body.errors&&body.errors.length)
            ? body.errors.map(function(er){return er.message}).join(', ')
            : 'There was an error trying to send your message. Please try again later.';
          out.classList.add('err');
        });
      }
    }).catch(function(){
      out.textContent='There was an error trying to send your message. Please try again later.';
      out.classList.add('err');
    }).finally(function(){
      if(btn) btn.disabled=false;          // re-enable for another message
    });
  });
}

/* ---- skin continuity: forward a #skin= fragment to blog/search-internal links ---- */
function initSkinLinks(){
  if(!/skin=(oxo|mono|ink|graphite|latex2)\b/.test(location.hash))return;
  document.querySelectorAll('a[href^="/blog/"],a[href^="/search/"]').forEach(a=>{if(!a.hash)a.href+=location.hash;});
}

document.addEventListener('DOMContentLoaded',()=>{initTyped();initCanvas();initSpy();initCount();initForm();initSkinLinks();});
})();
