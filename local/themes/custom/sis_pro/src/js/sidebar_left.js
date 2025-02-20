document.addEventListener('DOMContentLoaded', () => {
  const currentPath = window.location.pathname;

  // Highlight the active link based on the current page URL
  document.querySelectorAll('.menu-item > a[href]').forEach(link => {
    if (link.getAttribute('href') === currentPath) {
      link.classList.add('active');  // Apply active state
      expandParentMenus(link);       // Expand parent menus if nested
    }
  });

  // Add arrow indicators to menu items with children
  document.querySelectorAll('.menu-item.has-children > a').forEach(link => {
    const arrow = document.createElement('span');
    arrow.classList.add('arrow-indicator');
    link.appendChild(arrow); // Add arrow to the link

    // Toggle submenus on click
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const parentItem = link.parentElement;
      const submenu = parentItem.querySelector(':scope > .menu');

      if (submenu) {
        if (parentItem.classList.contains('open')) {
          collapseMenu(submenu, parentItem);
          arrow.classList.remove('rotated');
        } else {
          expandMenu(submenu, parentItem);
          arrow.classList.add('rotated');
        }
      }
    });
  });

  // Expand parent menus recursively
  function expandParentMenus(link) {
    let parent = link.closest('.menu-item');
    while (parent) {
      parent.classList.add('open');
      const submenu = parent.querySelector(':scope > .menu');
      const arrow = parent.querySelector('.arrow-indicator');
      if (submenu) {
        submenu.style.display = 'block';
        submenu.style.height = 'auto';
      }
      if (arrow) {
        arrow.classList.add('rotated'); // Rotate the arrow for open menus
      }
      parent = parent.parentElement.closest('.menu-item');
    }
  }

  // Expand submenu
  function expandMenu(submenu, parentItem) {
    parentItem.classList.add('open');
    submenu.style.display = 'block';
    const height = submenu.scrollHeight + 'px';
    submenu.style.overflow = 'hidden';
    submenu.style.height = '0px';

    requestAnimationFrame(() => {
      submenu.style.transition = 'height 300ms ease';
      submenu.style.height = height;
    });

    submenu.addEventListener('transitionend', () => {
      submenu.style.height = 'auto';
    }, { once: true });
  }

  // Collapse submenu
  function collapseMenu(submenu, parentItem) {
    const height = submenu.scrollHeight + 'px';
    submenu.style.height = height;

    requestAnimationFrame(() => {
      submenu.style.transition = 'height 300ms ease';
      submenu.style.height = '0px';
    });

    submenu.addEventListener('transitionend', () => {
      submenu.style.display = 'none';
      parentItem.classList.remove('open');
    }, { once: true });
  }
});
