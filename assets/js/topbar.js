const profileButton=document.getElementById("profileButton");
const profileMenu=document.getElementById("profileMenu");

if(profileButton && profileMenu){

    profileButton.addEventListener("click",function(e){

        e.stopPropagation();

        profileMenu.classList.toggle("show");

    });

    document.addEventListener("click",function(){

        profileMenu.classList.remove("show");

    });

}

const sidebar=document.getElementById("sidebar");
const toggle=document.getElementById("sidebarToggle");

if(toggle && sidebar){

    toggle.addEventListener("click",function(){

        sidebar.classList.toggle("show");

    });

}