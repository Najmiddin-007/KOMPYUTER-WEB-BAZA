// Dark / Light mode
(function(){
  const saved = localStorage.getItem('theme');
  if(saved) document.documentElement.setAttribute('data-theme', saved);
  const t = document.getElementById('darkToggle');
  if(t) t.addEventListener('click', ()=>{
    const cur = document.documentElement.getAttribute('data-theme')==='dark' ? '' : 'dark';
    if(cur) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
    localStorage.setItem('theme', cur);
  });
})();

// Animatsiya: ko'ringanda paydo bo'lish
document.addEventListener('DOMContentLoaded', ()=>{
  const els = document.querySelectorAll('.panel,.card,.tbl');
  const io = new IntersectionObserver(entries=>{
    entries.forEach(en=>{ if(en.isIntersecting){ en.target.style.opacity=1; en.target.style.transform='none'; io.unobserve(en.target);} });
  },{threshold:.08});
  els.forEach((el,i)=>{
    el.style.opacity=0; el.style.transform='translateY(14px)';
    el.style.transition='opacity .5s ease '+(i%6)*0.05+'s, transform .5s ease '+(i%6)*0.05+'s';
    io.observe(el);
  });
  // Bar animatsiyasi
  setTimeout(()=>{ document.querySelectorAll('.bar-fill').forEach(b=>{ const w=b.style.width; b.style.width='0'; setTimeout(()=>b.style.width=w,80); }); },150);
  // Skaner input: Enter dan keyin fokus qayta
  const si = document.getElementById('scanInput');
  if(si) setTimeout(()=>si.focus(), 300);
});
