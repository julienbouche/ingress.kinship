function searchPlayer() {
    var player_name = document.getElementsByName("searchname")[0].value;
    
    //trim player name
    player_name = player_name.replace(/^\s+|\s+$/g, '');
    
    //vérifie la présence de @ en début de chaine
    if (player_name.length > 0) {
        if (player_name[0] !='@') {
            player_name = '@'+ player_name;
        }
    }
    
    openPathToPlayer(player_name);
}

function openPathToPlayer(name){
    //on affiche tous les noeuds
    showAll(root);
    update(root);
    
    //on lance la recherche du joueur
    var found = startPlayerSearch(root, name);
    
    //on rafraichit l'affichage
    update(root);
    
    if (!found) {
        alert("le  joueur "+name+" n'a pas ete trouve.")
    }

}

function startPlayerSearch(root, name) {
    var found_sps = false;
    element = root;
    
    if (element.children) {
        for(var i=0; i<element.children.length; i++){
            var child = element.children[i];
            if (!found_sps) {
                //on vérifie le noeud courant
                if (name.toUpperCase() == child.name.toUpperCase()) {
                    found_sps = true;
                    hide(child);
                }
                else{
                    found_sps = hideIfDoesnotHaveChildrenNamed(child, name) || found_sps;
                }
            }
            else{
                hideAll(child);
            }   
        }
    }
    
    return found_sps;
}

function hideIfDoesnotHaveChildrenNamed(element, name){
    var found = false;

    if (element.children) {
        for(var i=0; i<element.children.length; i++){
            var child = element.children[i];

            if (name.toUpperCase() == child.name.toUpperCase()) {
                //on cache la lignée
                hideAll(child);
                
                found=true;
            }

        }
        
        //si on arrive ici, on a pas trouvé parmi les fils direct on relance
        for(var i=0; i<element.children.length; i++){
            var child = element.children[i];
            if (found == false) {
                found = hideIfDoesnotHaveChildrenNamed(child, name) || found;
            }
            else{
                hideAll(child);
                hide(child);
            }                        
        }
    }
    
    //si le joueur recherché n'a pas été trouvé, on ferme le noeud courant
    if (found == false) {
        hideAll(element);
    }
    
    return found;
}

function calculateNumberOfDescendants(d){
    var number =0;
    if (d.children) {
        for(var i=0; i<element.children.length; i++){
            var child = element.children[i];
            number+= calculateNumberOfDirectChildren(child);
        }
    }
    
    if (d._children) {
        for(var i=0; i<element.children.length; i++){
            var child = element.children[i];
            number += calculateNumberOfDirectChildren(child);
        }
    }
    
    number+= calculateNumberOfDirectChildren(d);
    
    return number;
}

function calculateNumberOfDirectChildren(d) {
    if (d._children) {
        return d._children.length;
    }
    
    if (d.children) {
        return d.children.length;
    }
    
    return 0;
}