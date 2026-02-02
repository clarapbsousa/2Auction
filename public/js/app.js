  function encodeForAjax(data) {
    if (data == null) return null;
    return Object.keys(data).map(function(k){
      return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
    }).join('&');
  }
  
  function sendAjaxRequest(method, url, data, handler) {
    let request = new XMLHttpRequest();
  
    request.open(method, url, true);
    request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.addEventListener('load', handler);
    request.send(encodeForAjax(data));
  }

  let currentIndex = 0;

  const contents = document.querySelectorAll('.carousel-content');
  const totalContents = contents.length;
  
  function showContent(index) {
    // Adjust the position of each content to create a sliding effect
    contents.forEach((content, i) => {
      if (i < index) {
        content.style.transform = 'translateX(-100%)'; // Slide to the left
      } else if (i > index) {
        content.style.transform = 'translateX(100%)'; // Slide to the right
      } else {
        content.style.transform = 'translateX(0)'; // Show the current content
      }
    });
  }
  
  function moveCarousel(direction) {
    if (direction === 'left') {
      currentIndex = (currentIndex === 0) ? totalContents - 1 : currentIndex - 1;
    } else if (direction === 'right') {
      currentIndex = (currentIndex === totalContents - 1) ? 0 : currentIndex + 1;
    }
    showContent(currentIndex);
  }
  
  // Initialize the first content
  showContent(currentIndex);
  
  addEventListeners();