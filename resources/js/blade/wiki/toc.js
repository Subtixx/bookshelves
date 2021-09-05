var tocContainer = document.getElementById("toc"); // Add this div to the HTML
var tocContainerSlideOver = document.getElementById("toc-slide-over"); // Add this div to the HTML

function createTableOfContent(node) {
  if (node) {
    window.addEventListener("DOMContentLoaded", function (event) {
      //Get all headings only from the actual contents.
      var contentContainer = document.getElementById("content"); // Add this div to the html
      var headings = contentContainer.querySelectorAll("h1,h2,h3,h4"); // You can do as many or as few headings as you need.

      // create ul element and set the attributes.
      var ul = document.createElement("ul");

      ul.setAttribute("id", "tocList");
      ul.setAttribute("class", "sidenav");

      // Loop through the headings NodeList
      for (i = 0; i <= headings.length - 1; i++) {
        var id = headings[i].innerHTML.toLowerCase().replace(/ /g, "-"); // Set the ID to the header text, all lower case with hyphens instead of spaces.
        var level = headings[i].localName.replace("h", ""); // Getting the header a level for hierarchy
        var title = headings[i].innerHTML; // Set the title to the text of the header

        let id_strip = id.replace(/(<([^>]+)>)/gi, "");
        id_strip = id_strip.replace(/[^a-zA-Z ]/g, "");

        headings[i].setAttribute("id", `heading-${id_strip}`); // Set header ID to its text in lower case text with hyphens instead of spaces.
        headings[i].classList.add("hero-bg");

        var li = document.createElement("li"); // create li element.
        li.setAttribute("class", "sidenav__item"); // Assign a class to the li

        var a = document.createElement("a"); // Create a link
        a.setAttribute("id", id_strip);
        a.setAttribute("href", `#heading-${id_strip}`); // Set the href to the heading ID
        a.setAttribute("class", "nav-link");
        a.innerHTML = title; // Set the link text to the heading text

        // Creeate the hierarchy
        // add a class for css
        if (level == 1) {
          li.appendChild(a); // Append the link to the list item
          ul.appendChild(li); // append li to ul.
        } else if (level == 2) {
          child = document.createElement("ul"); // Create a sub-list
          child.setAttribute("class", "sidenav__sublist");
          li.appendChild(a);
          child.appendChild(li);
          ul.appendChild(child);
        } else if (level == 3) {
          grandchild = document.createElement("ul");
          grandchild.setAttribute("class", "sidenav__sublist");
          li.appendChild(a);
          grandchild.appendChild(li);
          child.appendChild(grandchild);
        } else if (level == 4) {
          great_grandchild = document.createElement("ul");
          great_grandchild.setAttribute("class", "sidenav__sublist");
          li.append(a);
          great_grandchild.appendChild(li);
          grandchild.appendChild(great_grandchild);
        }
      }

      node.appendChild(ul); // add list to the container

      // Add a class to the first list item to allow for toggling active state.
      var links = node.getElementsByClassName("sidenav__item");

      // links[0].classList.add("active");

      // Loop through the links and add the active class to the active/clicked link
      for (var i = 0; i < links.length; i++) {
        links[i].addEventListener("click", function () {
          var active = document.getElementsByClassName("active");
          try {
            active[0].className = active[0].className.replace(" active", "");
          } catch (error) {}
          this.className += " active";
        });
      }

      //console.log(links);
      require("./scrollspy");
    });
  }
}

createTableOfContent(tocContainer);
createTableOfContent(tocContainerSlideOver);
