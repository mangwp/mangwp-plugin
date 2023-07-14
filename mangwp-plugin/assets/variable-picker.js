document.addEventListener('DOMContentLoaded', function() {
  var popup = document.createElement('div');
  popup.setAttribute('id', 'myPopup');
  popup.style.display = 'none';
  popup.style.position = 'fixed';
  popup.style.top = '50%';
  popup.style.left = '50%';
  popup.style.transform = 'translate(-50%, -50%)';
  popup.style.backgroundColor = '#23282d';
  popup.style.width = '50vw';
  popup.style.color = '#fff';
  popup.style.borderRadius = '5px';
  popup.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.3)';
  popup.style.zIndex = '9999';
  
  var innerDiv = document.createElement('div');
  innerDiv.classList.add('popup-variable-inner');
  popup.appendChild(innerDiv);
  innerDiv.style.padding = '20px';

  var title = document.createElement('h3');
  title.textContent = 'CSS Variables';
  title.style.marginTop = '0';
  title.style.marginBottom = '10px';
  innerDiv.appendChild(title);

  // Fetch CSS variables from the database and display them
  fetchCSSVariables();

  function fetchCSSVariables() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        var cssVariables = JSON.parse(xhr.responseText);
        displayCSSVariables(cssVariables);
      }
    };
    xhr.open('GET', mangwp_ajax.ajaxurl + '?action=get_css_variables', true);
    xhr.send();
  }

  function simulateValueChange(inputElement, newValue) {
    // Create an input event with the new value
    var inputEvent = new InputEvent('input', {
      bubbles: true,
      cancelable: true,
      composed: true,
      inputType: 'insertText',
      data: newValue,
    });

    // Set the value of the input element
    inputElement.value = newValue;

    // Dispatch the input event on the input element
    inputElement.dispatchEvent(inputEvent);
  }

  function displayCSSVariables(cssVariables) {
    var list = document.createElement('ul');
    list.setAttribute('id', 'variable-name');
    list.style.boxSizing = 'border-box'; // Set box-sizing to border-box
    list.style.padding = '0'; // Remove default padding

    cssVariables.forEach(function(variable) {
      var listItem = document.createElement('li');
      listItem.textContent = variable.name;
      listItem.style.display = 'inline-block'; // Display li elements inline
      listItem.style.border = '1px solid #ffffff'; // Add white border
      listItem.style.marginRight = '10px'; // Add right margin
      listItem.style.padding = '3px 8px'; // Add padding
      listItem.style.borderRadius = '5px'; // Add border radius
      listItem.style.fontSize = '1.2rem'; // Set font size
      listItem.style.marginBottom = '10px'; // Add bottom margin

      listItem.addEventListener('click', function() {
        var selectedItems = document.querySelectorAll('#variable-name li.selected');
        selectedItems.forEach(function(item) {
          item.classList.remove('selected');
        });

        listItem.classList.add('selected');
      });

      list.appendChild(listItem);
    });

    innerDiv.appendChild(list);

  }
   // Create close icon
    var closeIcon = document.createElement('span');
    closeIcon.textContent = '×';
    closeIcon.style.position = 'absolute';
    closeIcon.style.top = '10px';
    closeIcon.style.right = '10px';
    closeIcon.style.fontSize = '20px';
    closeIcon.style.cursor = 'pointer';
    popup.appendChild(closeIcon);

    // Create close button
    var closeButton = document.createElement('button');
    closeButton.textContent = 'Close';
    closeButton.style.marginTop = '10px';
    popup.appendChild(closeButton);

    // Add event listeners for close actions

  
  var overlay = document.createElement('div');
  overlay.setAttribute('id', 'myOverlay');
  overlay.style.display = 'none';
  overlay.style.position = 'fixed';
  overlay.style.top = '0';
  overlay.style.left = '0';
  overlay.style.width = '100%';
  overlay.style.height = '100%';
  overlay.style.backgroundColor = '#23282d';
  overlay.style.opacity = '0.8';
  overlay.style.zIndex = '9998';

  function closePopup(event) {
    if (event.target === popup || event.keyCode === 27) {
      popup.style.display = 'none';
      document.removeEventListener('click', closePopup);
      document.removeEventListener('keydown', closePopup);
      closeIcon.addEventListener('click', closePopup);
    closeButton.addEventListener('click', closePopup);
      document.querySelectorAll('div[data-control="number"]').forEach((div) => {
        div.classList.remove('variable-focus');
      });
    }
  }

  document.addEventListener('click', function(event) {
    var clickedElement = event.target;
    if (clickedElement.matches('div[data-control="number"] input')) {
      event.stopPropagation();
      popup.style.display = 'block';
      document.addEventListener('click', closePopup);
      document.addEventListener('keydown', closePopup);

      const parentDiv = clickedElement.parentElement;
      parentDiv.classList.add('variable-focus');

      // Remove 'variable-focus' class from other elements
      const allDivs = document.querySelectorAll('div[data-control="number"] input');
      allDivs.forEach(function(div) {
        if (div.parentElement !== parentDiv) {
          div.parentElement.classList.remove('variable-focus');
        }
      });
    }
  });

  document.addEventListener('click', function(event) {
    var clickedElement = event.target;
    if (clickedElement.matches('#variable-name li')) {
      var inputValue = clickedElement.textContent;
      console.log(inputValue);
      var variableFocus = document.querySelector('div[data-control="number"].variable-focus');
      if (variableFocus) {
        var input = variableFocus.getElementsByTagName('input')[0];
        if (input) {
          input.value = inputValue;
          input.placeholder = inputValue;
          var newValue = inputValue;
          simulateValueChange(input, newValue);
        }
      }
    }
  });

  var initialMouseX, initialMouseY, initialPopupX, initialPopupY;

  popup.addEventListener('mousedown', startDrag);

  function startDrag(event) {
    // Store the initial positions
    initialMouseX = event.clientX;
    initialMouseY = event.clientY;
    initialPopupX = popup.offsetLeft;
    initialPopupY = popup.offsetTop;

    // Add event listeners to track the mouse movement
    document.addEventListener('mousemove', dragPopup);
    document.addEventListener('mouseup', stopDrag);
  }

  function dragPopup(event) {
    // Calculate the distance moved by the mouse
    var deltaX = event.clientX - initialMouseX;
    var deltaY = event.clientY - initialMouseY;

    // Calculate the new position of the popup
    var newPopupX = initialPopupX + deltaX;
    var newPopupY = initialPopupY + deltaY;

    // Update the position of the popup
    popup.style.left = newPopupX + 'px';
    popup.style.top = newPopupY + 'px';
  }

  function stopDrag() {
    // Remove the event listeners
    document.removeEventListener('mousemove', dragPopup);
    document.removeEventListener('mouseup', stopDrag);
  }

  document.body.appendChild(popup);
});
