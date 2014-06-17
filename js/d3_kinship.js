
// Toggle children.
function toggle(d) {
  if (d.children) {
    d._children = d.children;
    d.children = null;
  } else {
    d.children = d._children;
    d._children = null;
  }
}

function toggleAll(d) {
    if (d.children) {
        d.children.forEach(toggleAll);
        toggle(d);
    }
}

function hide(d) {
    if (d.children) {
        //code
        d._children = d.children;
        d.children = null;
    }
}

function hideAll(d) {
    if (d.children) {
        d.children.forEach(hideAll);
        hide(d);
    }
}

function show(d) {
    if (d.children == null) {
        d.children = d._children;
        d._children = null;
    }
}

function showAll(d) {
    show(d);
    
    //on relance si des enfants existent
    if (d.children) {
      d.children.forEach(showAll);
    }
}