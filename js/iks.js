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
function calculateNumberOfVisibleDescendants(d){
    var number =0;
    if (d.children) {
        for(var i=0; i<d.children.length; i++){
            var child = d.children[i];
            number+= calculateNumberOfVisibleDescendants(child);
        }
    }
    
    number+= calculateNumberOfDirectVisibleChildren(d);
    return number;
}

function calculateNumberOfDescendants(d){
    var number =0;
    if (d.children) {
        for(var i=0; i<d.children.length; i++){
            var child = d.children[i];
            number+= calculateNumberOfDescendants(child);
        }
    }
    
    if (d._children) {
        for(var i=0; i<d._children.length; i++){
            var child = d._children[i];
            number += calculateNumberOfDescendants(child);
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

function calculateNumberOfDirectVisibleChildren(d) {
    
    if (d.children) {
        return d.children.length;
    }
    
    return 0;
}



            
function display_stats() {
    menu = document.getElementById("statsSideMenu");
    if (menu) {
        if (!menu.className.match(/(?:^|\s)visible(?!\S)/)) {
            menu.className="sidemenu right visible";
        }
        else menu.className="sidemenu right";
    }
}

function calc_stats(element) {
    //rafraichir la valeur de la profondeur maximale de l'arbre en fonction de ce qui est affiché
    calc_stats_profmax(element);
    
    calc_stats_largmax(element);
    
    //compte le nb de joueur affichés
    calc_nb_displayed_player(element);
}

function calc_nb_displayed_player(element) {
    var max=0, max_v = 0;
    
    
    max = calculateNumberOfDescendants(element);
    max_v = calculateNumberOfVisibleDescendants(element);
    //alert(element.name +" : "+max_v+"/"+max);
    dom_stat_player = document.getElementById("stats_visible_player");
    
    if (dom_stat_player) {
        dom_stat_player.innerHTML = max_v+ " / "+ max;
    }
}

function calc_stats_profmax(element){
    var max = 0;
    max = calc_stats_profmax_r(element);
    dom_stat_profmax = document.getElementById("stats_profmax");
    if (dom_stat_profmax) {
        dom_stat_profmax.innerHTML = max-1;
    }
}

function calc_stats_profmax_r(element) {
    var max = 0;
    var tmp = 0;
    
    if (element.children) {
        for(var i=0; i<element.children.length; i++){
            tmp = calc_stats_profmax_r(element.children[i]);
            if (tmp>max) {
                max = tmp;
            }
        }
    }
    
    //on retourne le nombre max + 1 pour le noeud courant
    max+=1;
    
    return max;
}

function calc_stats_largmax(element){
    var max = 0;
    //la fratrie du noeud racine ne compte pas vraiment...
    
    if (element.children) {
        for(var i=0; i<element.children.length;i++){
            max = Math.max(max, calc_stats_largmax_r(element.children[i]));
        }
    }
    
    //max = calc_stats_largmax_r(element);
    dom_stat_largmax = document.getElementById("stats_largmax");
    if (dom_stat_largmax) {
        if (max>1){
            dom_stat_largmax.innerHTML = max;
        }
        else
            dom_stat_largmax.innerHTML = "aucune fratrie";
    }
}

function calc_stats_largmax_r(element){
    if (element.children) {
        var max = 0;
        //la fratrie du noeud racine ne compte pas vraiment...
        
        if (element.children) {
            for(var i=0; i<element.children.length;i++){
                max = Math.max(max, calc_stats_largmax_r(element.children[i]));
            }
        }
        return Math.max(max, element.children.length);
    }
    else return 0;
}