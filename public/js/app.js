const body = document.querySelector("body");
const slideNav = document.getElementById("slide-nav");
const sideNav = document.getElementById("side-nav");
const hamburger = document.getElementById("hamburger");
const megaburger = document.getElementById("megaburger");
const meganav = document.querySelector('.meganav-links');
const html = document.querySelector('html');
let slideNavOpen = false;
let openingModal = false;

function toggleScheme() {
  const currentScheme = html.getAttribute('data-scheme');
  const newScheme = currentScheme === 'light' ? 'dark' : 'light';
  html.setAttribute('data-scheme', newScheme);
}

function previewAvatar(el) {
  const file = el.files[0];
  if (file) document.getElementById('avatar-preview').innerHTML =
    '<img src="' + URL.createObjectURL(file) + '" alt="preview">';
}

function getElement(elRef) {
  const firstChar = elRef.substring(0, 1);
  if (firstChar === ".") {
    return document.querySelector(elRef); // Changed to querySelector for consistency
  } else {
    return document.getElementById(elRef);
  }
}

function toggleSlideNav() {
  if (slideNav.style.opacity === 1) {
    hamburger.classList.remove("show-burger");
    slideNav.classList.remove("isShown");
  } else {
    hamburger.classList.toggle("show-burger");
    slideNav.classList.toggle("isShown");
  }
}

function toggleSideMenu() {
  if (sideNav.style.opacity === 1) {
    hamburger.classList.remove("show-burger");
    sideNav.classList.remove('isShown');
    slideNav.classList.remove("isShown");
  } else {
    hamburger.classList.toggle("show-burger");
    sideNav.classList.toggle('isShown');
    slideNav.classList.toggle("isShown");
  }
}

function openSlideNav() {
  slideNav.style.opacity = 1;
  hamburger.classList.toggle("show-burger");
  slideNav.style.width = "75%";
  slideNav.style.zIndex = 2;
  setTimeout(() => {
    slideNavOpen = true;
  }, 200);
}

function closeSlideNav() {
  slideNav.style.opacity = 0;
  hamburger.classList.remove("show-burger");
  slideNav.style.width = "0";
  slideNav.style.zIndex = "-1";
  slideNavOpen = false;
}

function openModal(modalId) {
  openingModal = true;
  setTimeout(() => {
    openingModal = false;
  }, 100);
  let pageOverlay = document.getElementById("overlay");
  if (!pageOverlay) {
    const modalContainer = document.createElement("div");
    modalContainer.setAttribute("id", "modal-container");
    modalContainer.setAttribute("style", "z-index: 9999;");
    body.append(modalContainer);
    pageOverlay = document.createElement("div");
    pageOverlay.setAttribute("id", "overlay");
    pageOverlay.setAttribute("style", "z-index: 2");
    body.append(pageOverlay);
    const targetModal = getElement(modalId);
    if (!targetModal) return;
    const targetModalContent = targetModal.innerHTML;
    targetModal.remove();
    const newModal = document.createElement("div");
    newModal.setAttribute("class", "modal");
    newModal.setAttribute("id", modalId);
    newModal.style.zIndex = 4;
    newModal.innerHTML = targetModalContent;
    modalContainer.appendChild(newModal);
    
    // Use requestAnimationFrame to ensure the modal is in the DOM before we try to show it
    requestAnimationFrame(() => {
      newModal.style.display = 'block';
      newModal.style.opacity = 1;
      
      // Get the computed style of the modal
      const style = getComputedStyle(newModal);
      
      // Use the custom property if it's set, otherwise fall back to the default
      const marginTop = style.getPropertyValue('--modal-margin-top').trim() || '12vh';
      
      newModal.style.marginTop = marginTop;
    });
    return newModal;
  }
  return null;
}

function closeModal() {
  const modalContainer = document.getElementById("modal-container");
  if (modalContainer) {
    const openModal = modalContainer.firstChild;
    openModal.style.zIndex = "-4";
    openModal.style.opacity = 0;
    openModal.style.marginTop = "12vh";
    openModal.style.display = "none";
    document.body.appendChild(openModal);
    modalContainer.remove();

    const overlay = document.getElementById("overlay");
    if (overlay) {
      overlay.remove();
    }

    // Dispatch a custom event indicating modal closure
    const event = new Event('modalClosed', { bubbles: true, cancelable: true });
    document.dispatchEvent(event);
  }
}

function autoPopulateSlideNav() {
  const slideNavLinks = document.querySelector("#slide-nav ul");
  if (slideNavLinks && slideNavLinks.getAttribute("auto-populate") === "true") {
    const navLinks = document.querySelector("#top-nav");
    if (navLinks) {
      slideNavLinks.innerHTML = navLinks.innerHTML;
    }
  }
}

function handleSlideNavClick(event) {
  if (slideNavOpen && event.target.id !== "open-btn" && !slideNav.contains(event.target)) {
    closeSlideNav();
  }
}

function handleEscapeKey(event) {
  if (event.key === 'Escape') {
    const modalContainer = document.getElementById("modal-container");
    if (modalContainer) {
      closeModal();
    }
  }
}

function handleModalClick(event) {
  if (openingModal === true) {
    return;
  }

  const modalContainer = document.getElementById("modal-container");
  if (modalContainer) {
    const modal = modalContainer.querySelector('.modal');
    if (modal && !modal.contains(event.target)) {
      closeModal();
    }
  }
}

// Global MX loading bar — intercepts all XHR requests
(function () {
  const loader = document.getElementById('mx-loader');
  if (!loader) return;
  let active = 0;
  const show = () => loader.classList.remove('mx-indicator-hidden');
  const hide = () => { if (--active <= 0) { active = 0; loader.classList.add('mx-indicator-hidden'); } };
  const origOpen = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function () {
    this.addEventListener('loadstart', () => { active++; show(); });
    this.addEventListener('loadend', hide);
    origOpen.apply(this, arguments);
  };
})();

// Initialize
autoPopulateSlideNav();

// Add event listeners
body.addEventListener("click", (event) => {
  handleSlideNavClick(event);
  handleModalClick(event);
});

document.addEventListener('keydown', handleEscapeKey);