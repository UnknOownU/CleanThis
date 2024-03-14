
document.addEventListener("DOMContentLoaded", function() {
    var currentLocation = window.location.pathname;
    var navLinks = document.querySelectorAll(".nav-content li a");
  
    navLinks.forEach(function(link) {
      if (link.getAttribute("href") === currentLocation) {
        link.classList.add("active");
      }
    });
  });