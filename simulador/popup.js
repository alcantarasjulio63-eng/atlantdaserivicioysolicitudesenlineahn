(function(){
  var style = document.createElement('style');
  style.textContent = [
    '#__pop_ov{position:fixed;inset:0;z-index:99999;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .35s}',
    '#__pop_ov.in{opacity:1}',
    '#__pop_box{background:#fff;border-radius:12px;padding:28px 32px;max-width:320px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35);transform:translateY(18px);transition:transform .35s}',
    '#__pop_ov.in #__pop_box{transform:translateY(0)}',
    '#__pop_box p{font-family:-apple-system,"Segoe UI",Roboto,sans-serif;font-size:15px;font-weight:500;color:#111;line-height:1.5;margin:0}'
  ].join('');
  document.head.appendChild(style);

  var ov = document.createElement('div'); ov.id = '__pop_ov';
  var bx = document.createElement('div'); bx.id = '__pop_box';
  var tx = document.createElement('p');   tx.textContent = 'Identifícate para validar tu identidad';
  bx.appendChild(tx); ov.appendChild(bx); document.body.appendChild(ov);

  setTimeout(function(){
    ov.classList.add('in');
    setTimeout(function(){
      ov.classList.remove('in');
      setTimeout(function(){ ov.parentNode && ov.parentNode.removeChild(ov); }, 400);
    }, 3000);
  }, 1000);
})();
