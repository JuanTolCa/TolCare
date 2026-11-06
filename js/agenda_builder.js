function openTab(id){

document.querySelectorAll('.tabcontent').forEach(t=>{
t.classList.remove('active');
});

document.querySelectorAll('.tab').forEach(t=>{
t.classList.remove('active');
});

document.getElementById(id).classList.add('active');
document.getElementById("tab_"+id).classList.add('active');

}