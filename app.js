document.addEventListener('DOMContentLoaded',()=>{
  const menu=document.querySelector('[data-mobile-menu]'); const nav=document.querySelector('[data-nav]');
  menu?.addEventListener('click',()=>nav?.classList.toggle('open'));
  document.querySelectorAll('[data-auto-dismiss]').forEach(el=>setTimeout(()=>el.remove(),4500));
  document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm))e.preventDefault()}));
  document.querySelectorAll('form[data-validate]').forEach(form=>form.addEventListener('submit',e=>{
    let ok=true; form.querySelectorAll('[required]').forEach(input=>{input.classList.remove('invalid'); if(!input.value.trim()){ok=false;input.classList.add('invalid')}});
    const pw=form.querySelector('input[name="password"]'); const cp=form.querySelector('input[name="password_confirm"]');
    if(pw && pw.value.length<8){ok=false;pw.classList.add('invalid')}
    if(pw && cp && pw.value!==cp.value){ok=false;cp.classList.add('invalid')}
    if(!ok){e.preventDefault(); alert('Please check the highlighted fields and try again.');}
  }));
  const preview=document.querySelector('[data-image-preview]'); const file=document.querySelector('[data-image-input]');
  file?.addEventListener('change',()=>{const f=file.files?.[0]; if(f&&preview){preview.src=URL.createObjectURL(f)}});
  document.querySelectorAll('[data-search-input]').forEach(input=>input.addEventListener('input',()=>{
    const target=document.querySelector(input.dataset.searchInput); if(!target)return;
    const q=input.value.toLowerCase(); target.querySelectorAll('[data-searchable]').forEach(el=>el.style.display=el.textContent.toLowerCase().includes(q)?'':'none');
  }));
});

function initHero3D(){
  const canvas=document.querySelector('#hero-canvas'); if(!canvas||typeof THREE==='undefined')return;
  const scene=new THREE.Scene();
  const camera=new THREE.PerspectiveCamera(42,canvas.clientWidth/canvas.clientHeight,.1,100); camera.position.set(0,0,8);
  const renderer=new THREE.WebGLRenderer({canvas,alpha:true,antialias:true}); renderer.setPixelRatio(Math.min(devicePixelRatio,2)); renderer.setSize(canvas.clientWidth,canvas.clientHeight,false);
  const group=new THREE.Group(); scene.add(group);
  const geo=new THREE.IcosahedronGeometry(2.05,2); const mat=new THREE.MeshPhysicalMaterial({color:0x55e8e8,wireframe:true,transparent:true,opacity:.75,roughness:.25,metalness:.45}); const orb=new THREE.Mesh(geo,mat); group.add(orb);
  const inner=new THREE.Mesh(new THREE.IcosahedronGeometry(1.55,2),new THREE.MeshBasicMaterial({color:0x6c8cff,wireframe:true,transparent:true,opacity:.22})); group.add(inner);
  const nodes=new THREE.Group();
  for(let i=0;i<55;i++){const a=Math.random()*Math.PI*2,b=Math.acos(2*Math.random()-1),r=2.35+Math.random()*.65;const p=new THREE.Vector3(r*Math.sin(b)*Math.cos(a),r*Math.sin(b)*Math.sin(a),r*Math.cos(b));const dot=new THREE.Mesh(new THREE.SphereGeometry(.025,6,6),new THREE.MeshBasicMaterial({color:0x9dffff}));dot.position.copy(p);nodes.add(dot)} group.add(nodes);
  const light=new THREE.PointLight(0x55e8e8,18,15);light.position.set(3,3,4);scene.add(light);const light2=new THREE.PointLight(0x765cff,12,15);light2.position.set(-4,-2,2);scene.add(light2);
  let mx=0,my=0; window.addEventListener('pointermove',e=>{mx=(e.clientX/innerWidth-.5)*.5;my=(e.clientY/innerHeight-.5)*.4});
  const tick=()=>{requestAnimationFrame(tick);group.rotation.y+=.0025;group.rotation.x+=.001;group.rotation.y+=(mx-group.rotation.y)*.003;group.rotation.x+=(my-group.rotation.x)*.003;renderer.render(scene,camera)}; tick();
  new ResizeObserver(()=>{const w=canvas.clientWidth,h=canvas.clientHeight;camera.aspect=w/h;camera.updateProjectionMatrix();renderer.setSize(w,h,false)}).observe(canvas);
}
window.addEventListener('load',initHero3D);
