<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Konten - OSPay</title>
<style>
  :root { --b:#2563eb; --bd:#e2e8f0; --mut:#64748b; }
  * { box-sizing: border-box; }
  body { font-family: system-ui, sans-serif; margin:0; background:#f8fafc; color:#0f172a; }
  header { background:#fff; border-bottom:1px solid var(--bd); padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
  main { max-width:860px; margin:20px auto; padding:0 16px; }
  .card { background:#fff; border:1px solid var(--bd); border-radius:10px; padding:16px; margin-bottom:14px; }
  label { display:block; font-size:13px; color:var(--mut); margin:10px 0 4px; }
  input, textarea, select { width:100%; padding:8px 10px; border:1px solid var(--bd); border-radius:8px; font:inherit; }
  textarea { min-height:90px; resize:vertical; }
  button { padding:8px 14px; border:0; border-radius:8px; background:var(--b); color:#fff; font:inherit; cursor:pointer; }
  button.sec { background:#e2e8f0; color:#0f172a; } button.del { background:#dc2626; }
  .tabs { display:flex; gap:6px; margin-bottom:14px; }
  .tabs button { background:#e2e8f0; color:#0f172a; } .tabs button.on { background:var(--b); color:#fff; }
  .row { display:flex; gap:8px; align-items:center; } .row > * { flex:1; } .row > button { flex:none; }
  .msg { padding:8px 12px; border-radius:8px; margin-bottom:12px; font-size:14px; }
  .ok { background:#dcfce7; } .err { background:#fee2e2; }
  .hide { display:none; } small { color:var(--mut); }
  .sec-box { border:1px dashed var(--bd); border-radius:8px; padding:10px; margin-top:10px; }
</style>
</head>
<body>
<header><strong>Kelola Konten OSPay</strong><button id="logout" class="sec hide">Keluar</button></header>
<main>
  <div id="msg"></div>

  <div id="login" class="card">
    <h3>Masuk (superadmin)</h3>
    <label>Email</label><input id="email" type="email">
    <label>Password</label><input id="password" type="password">
    <p><button id="doLogin">Masuk</button></p>
  </div>

  <div id="app" class="hide">
    <div class="tabs">
      <button data-tab="faqs" class="on">FAQ</button>
      <button data-tab="pages">Kebijakan & Syarat</button>
      <button data-tab="info">Tentang Aplikasi</button>
    </div>
    <div id="view"></div>
  </div>
</main>

<script>
const API = '/api';
let token = localStorage.getItem('adm_token');
const $ = (id) => document.getElementById(id);
const esc = (s) => String(s ?? '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

function say(text, ok = true) {
  $('msg').innerHTML = text ? `<div class="msg ${ok ? 'ok' : 'err'}">${esc(text)}</div>` : '';
  if (text) setTimeout(() => $('msg').innerHTML = '', 4000);
}

async function api(method, path, body) {
  const res = await fetch(API + path, {
    method,
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json',
               ...(token ? { 'Authorization': 'Bearer ' + token } : {}) },
    body: body ? JSON.stringify(body) : undefined,
  });
  const json = await res.json().catch(() => ({}));
  if (!res.ok) {
    const first = json.errors ? Object.values(json.errors)[0][0] : null;
    throw new Error(first || json.message || 'Gagal (' + res.status + ')');
  }
  return json;
}

function showApp(on) {
  $('login').classList.toggle('hide', on);
  $('app').classList.toggle('hide', !on);
  $('logout').classList.toggle('hide', !on);
}

$('doLogin').onclick = async () => {
  try {
    const r = await api('POST', '/login', { email: $('email').value, password: $('password').value });
    if (r.user?.role !== 'superadmin') throw new Error('Akun ini bukan superadmin.');
    token = r.token; localStorage.setItem('adm_token', token);
    showApp(true); openTab('faqs');
  } catch (e) { say(e.message, false); }
};
$('logout').onclick = () => { localStorage.removeItem('adm_token'); token = null; showApp(false); };

document.querySelectorAll('.tabs button').forEach(b => b.onclick = () => openTab(b.dataset.tab));
function openTab(t) {
  document.querySelectorAll('.tabs button').forEach(b => b.classList.toggle('on', b.dataset.tab === t));
  ({ faqs: loadFaqs, pages: loadPages, info: loadInfo })[t]();
}

/* ------------------------------------------------------------ FAQ */
async function loadFaqs() {
  try {
    const { data } = await api('GET', '/admin/faqs');
    $('view').innerHTML = `
      <div class="card"><h3>Tambah FAQ</h3>
        <label>Pertanyaan</label><input id="nq">
        <label>Jawaban</label><textarea id="na"></textarea>
        <p><button onclick="saveFaq()">Tambah</button></p></div>` +
      data.map(f => `<div class="card" data-id="${f.id}">
        <label>Pertanyaan</label><input class="q" value="${esc(f.question)}">
        <label>Jawaban</label><textarea class="a">${esc(f.answer)}</textarea>
        <div class="row"><div><label>Urutan</label><input class="so" type="number" value="${f.sort_order}"></div>
          <div><label>Tampil</label><select class="ac"><option value="1" ${f.is_active ? 'selected' : ''}>Ya</option><option value="0" ${f.is_active ? '' : 'selected'}>Tidak</option></select></div></div>
        <p><button onclick="saveFaq(${f.id})">Simpan</button> <button class="del" onclick="delFaq(${f.id})">Hapus</button></p></div>`).join('');
  } catch (e) { say(e.message, false); }
}
async function saveFaq(id) {
  try {
    if (id) {
      const c = document.querySelector(`[data-id="${id}"]`);
      await api('PUT', '/admin/faqs/' + id, { question: c.querySelector('.q').value, answer: c.querySelector('.a').value,
        sort_order: +c.querySelector('.so').value, is_active: c.querySelector('.ac').value === '1' });
    } else {
      await api('POST', '/admin/faqs', { question: $('nq').value, answer: $('na').value });
    }
    say('FAQ tersimpan.'); loadFaqs();
  } catch (e) { say(e.message, false); }
}
async function delFaq(id) {
  if (!confirm('Hapus FAQ ini?')) return;
  try { await api('DELETE', '/admin/faqs/' + id); say('FAQ dihapus.'); loadFaqs(); }
  catch (e) { say(e.message, false); }
}

/* ---------------------------------------------------------- Halaman */
async function loadPages() {
  try {
    const { data } = await api('GET', '/admin/pages');
    $('view').innerHTML = `<div class="card"><h3>Halaman dokumen</h3>
      ${data.map(p => `<p class="row"><span>${esc(p.title)} <small>(${esc(p.slug)})</small></span>
        <button class="sec" onclick="editPage('${esc(p.slug)}')">Edit</button></p>`).join('') || '<small>Belum ada.</small>'}
      <hr><button class="sec" onclick="editPage('')">+ Halaman baru</button></div><div id="editor"></div>`;
  } catch (e) { say(e.message, false); }
}
let sections = [];
async function editPage(slug) {
  let p = { slug, title: '', intro: '', is_active: true, sections: [] };
  if (slug) { try { p = (await api('GET', '/admin/pages/' + slug)).data; } catch (e) { return say(e.message, false); } }
  sections = (p.sections || []).map(s => ({ title: s.title, text: (s.paragraphs || []).join('\n') }));
  $('editor').innerHTML = `<div class="card"><h3>${slug ? 'Edit' : 'Halaman baru'}</h3>
    <label>Slug ${slug ? '' : '(mis. privacy-policy, terms)'}</label><input id="pslug" value="${esc(p.slug)}" ${slug ? 'readonly' : ''}>
    <label>Judul</label><input id="ptitle" value="${esc(p.title)}">
    <label>Pengantar</label><textarea id="pintro">${esc(p.intro)}</textarea>
    <label>Tampil</label><select id="pact"><option value="1" ${p.is_active ? 'selected' : ''}>Ya</option><option value="0" ${p.is_active ? '' : 'selected'}>Tidak</option></select>
    <div id="secs"></div>
    <p><button class="sec" onclick="addSec()">+ Bagian</button></p>
    <p><button onclick="savePage()">Simpan</button> ${slug ? `<button class="del" onclick="delPage('${esc(slug)}')">Hapus halaman</button>` : ''}</p></div>`;
  drawSecs();
}
function drawSecs() {
  $('secs').innerHTML = sections.map((s, i) => `<div class="sec-box">
    <label>Judul bagian</label><input value="${esc(s.title)}" oninput="sections[${i}].title=this.value">
    <label>Isi (satu paragraf per baris)</label><textarea oninput="sections[${i}].text=this.value">${esc(s.text)}</textarea>
    <button class="sec" onclick="sections.splice(${i},1);drawSecs()">Hapus bagian</button></div>`).join('');
}
function addSec() { sections.push({ title: '', text: '' }); drawSecs(); }
async function savePage() {
  try {
    const slug = $('pslug').value.trim();
    if (!slug) throw new Error('Slug wajib diisi.');
    await api('PUT', '/admin/pages/' + encodeURIComponent(slug), {
      title: $('ptitle').value, intro: $('pintro').value, is_active: $('pact').value === '1',
      sections: sections.map(s => ({ title: s.title, paragraphs: s.text.split('\n').map(x => x.trim()).filter(Boolean) })),
    });
    say('Halaman tersimpan.'); loadPages();
  } catch (e) { say(e.message, false); }
}
async function delPage(slug) {
  if (!confirm('Hapus halaman ini?')) return;
  try { await api('DELETE', '/admin/pages/' + slug); say('Halaman dihapus.'); loadPages(); }
  catch (e) { say(e.message, false); }
}

/* ------------------------------------------------------------- Info */
let hl = [];
async function loadInfo() {
  try {
    const d = (await api('GET', '/admin/app-info')).data || {};
    hl = d.highlights || [];
    $('view').innerHTML = `<div class="card"><h3>Tentang aplikasi</h3>
      <label>Deskripsi</label><textarea id="idesc">${esc(d.description)}</textarea>
      <label>Versi terbaru</label><input id="iver" value="${esc(d.latest_version)}">
      <label>WhatsApp</label><input id="iwa" value="${esc(d.support?.whatsapp)}">
      <label>Email</label><input id="imail" value="${esc(d.support?.email)}">
      <label>Jam operasional</label><input id="ihours" value="${esc(d.support?.hours)}">
      <h4>Keunggulan</h4><div id="hls"></div>
      <p><button class="sec" onclick="hl.push({icon:'info',title:'',description:''});drawHl()">+ Keunggulan</button></p>
      <p><button onclick="saveInfo()">Simpan</button></p></div>`;
    drawHl();
  } catch (e) { say(e.message, false); }
}
function drawHl() {
  $('hls').innerHTML = hl.map((h, i) => `<div class="sec-box">
    <label>Ikon (bolt, apps, receipt, wallet, info)</label><input value="${esc(h.icon)}" oninput="hl[${i}].icon=this.value">
    <label>Judul</label><input value="${esc(h.title)}" oninput="hl[${i}].title=this.value">
    <label>Deskripsi</label><input value="${esc(h.description)}" oninput="hl[${i}].description=this.value">
    <button class="sec" onclick="hl.splice(${i},1);drawHl()">Hapus</button></div>`).join('');
}
async function saveInfo() {
  try {
    await api('PUT', '/admin/app-info', {
      description: $('idesc').value, latest_version: $('iver').value || null,
      support: { whatsapp: $('iwa').value, email: $('imail').value || null, hours: $('ihours').value },
      highlights: hl,
    });
    say('Info aplikasi tersimpan.');
  } catch (e) { say(e.message, false); }
}

if (token) { showApp(true); openTab('faqs'); }
</script>
</body>
</html>
