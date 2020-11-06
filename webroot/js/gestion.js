function openNav() {
	var width = document.getElementById("mySidenav").clientWidth;
	  if(width == 0){
		  document.getElementById("mySidenav").style.width = "10vw";
		  document.getElementById("conteneur").style.transform = "translateX(2vw)";
	  	}
	  else {
		  document.getElementById("mySidenav").style.width = "0vw";
		  document.getElementById("conteneur").style.transform = "translateX(0vw)";	
	  }
}
