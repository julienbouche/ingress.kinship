
<?php
require_once("include/db.php");


if(isset($_POST['username']) && isset($_POST['parentusername'])){
    db_connect();
    
    $username = trim(mysql_real_escape_string($_POST['username']));
    $parent = trim(mysql_real_escape_string($_POST['parentusername']));
    
    if($username[0]=="@"){
        $username = substr($username, 1);
    }
    
    if($parent[0]=="@"){
        $parent = substr($parent, 1);
    }
    
    
    if(strlen($username)){
        //on vérifie que l'utilisateur n'est pas encore présent
        $sql = "SELECT * FROM USER WHERE UCASE(name)=UCASE('$username')";
        $result = mysql_query($sql);    
        if(mysql_num_rows($result)==0){
            //injection par défaut
            $sql_inj = "INSERT INTO USER(name) VALUES('$username')";
            if(strlen($parent)>0){
                //on récupère l'id du père
                $sql = "SELECT id_user FROM USER WHERE UCASE(name) = UCASE('$parent')";
                $result = mysql_query($sql);
                if($result && mysql_num_rows($result)){
                    $id_pere = mysql_result($result,0,'id_user');
                    $sql_inj = "INSERT INTO USER (name, id_parent) VALUES('$username', $id_pere)";
                }
                else{
                    //le père n'existe pas on l'ajoute
                    $sql_inj_p = "INSERT INTO USER(name) VALUES('$parent')";
                    mysql_query($sql_inj_p);
                    $sql = "SELECT id_user FROM USER WHERE UCASE(name) = UCASE('$parent')";
                    $result = mysql_query($sql);
                    if($result && mysql_num_rows($result)){
                        $id_pere = mysql_result($result,0,'id_user');
                        $sql_inj = "INSERT INTO USER (name, id_parent) VALUES('$username', $id_pere)";
                    }
                }
            }
        
            //injection nouveau joueur
            mysql_query($sql_inj);
        }
        else{
            //il faut faire l'update du joueur
            if(strlen($parent)>0){
                //on récupère l'id du père
                $sql = "SELECT id_user FROM USER WHERE UCASE(name) = UCASE('$parent')";
                $result = mysql_query($sql);
                if($result && mysql_num_rows($result)){
                    $id_pere = mysql_result($result,0,'id_user');
                    $sql_inj = "UPDATE USER SET id_parent=$id_pere WHERE UCASE(name)=UCASE('$username')";
                }
                else{
                    //le père n'existe pas on l'ajoute
                    $sql_inj_p = "INSERT INTO USER(name) VALUES('$parent')";
                    mysql_query($sql_inj_p);
                    $sql = "SELECT id_user FROM USER WHERE UCASE(name) = UCASE('$parent')";
                    $result = mysql_query($sql);
                    if($result && mysql_num_rows($result)){
                        $id_pere = mysql_result($result,0,'id_user');
                        $sql_inj = "UPDATE USER SET id_parent=$id_pere WHERE UCASE(name)=UCASE('$username')";
                    }
                }
            }
            else{
                $sql_inj = "UPDATE USER SET id_parent=NULL WHERE UCASE(name)=UCASE('$username')";
            }
            error_log($sql_inj);
            //update du joueur
            mysql_query($sql_inj);
        }
    }
}

?>
<html>
    
    <head>
        <title>Ingress Kinship Tree</title>
        <meta charset="utf-8" />
        <link  rel="stylesheet" type="text/css" href="css/iks.css" media="screen, projection" />
        <script type="text/javascript" src="js/d3-3.4.8/d3.min.js"></script>
        <script type="text/javascript" src="js/iks.js"></script>
        <script type="text/javascript" src="js/d3_kinship.js"></script>
        <link rel="icon" type="image/png" href="images/resistant.png" />
    </head>
    <body>
        <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" style="float:left;width:40%">
            <fieldset>
                <legend>Ajouter/Modifier un joueur</legend>
                <input type="text" placeholder="@username" name="username"/> initié par <input type="text" placeholder="@username" name="parentusername"/>
                <input type="submit" value="Ajouter" />
            </fieldset>
        </form>
        
        <form style="float:left; width:40%" onsubmit="searchPlayer();return false;">
            <fieldset>
                <legend>Rechercher un joueur</legend>
                <input type="text" name="searchname" placeholder="@playername" />
                <input type="submit" value="Rechercher" onclick="searchPlayer();"/>
                <input type="button" value="Afficher tous" onclick="showAll(root);update(root);" />
                <input type="button" value="Cacher tous" onclick="hideAll(root);update(root);" />
                
            </fieldset>            
        </form>
        
        <script>
            function maximum(a, b) {
                return a<b? b: a;
            }
            
            function zoomed() {
                vis.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
            }
                
            var m = [20, 20, 20, 20],
                w = maximum(window.innerWidth - m[1] - m[3], 1000),
                h = window.innerHeight - m[0] - m[2],
                i = 0,
                mult_x=1.5, mult_y=1,
                root;
            
            
            var tree = d3.layout.tree()
                .size([h, w]);
            
            var zoom = d3.behavior.zoom()
                .scaleExtent([0.5, 10])
                .on("zoom", zoomed);
            
            var diagonal = d3.svg.diagonal()
                .projection(function(d) { return [d.x*mult_x, d.y]; });
            
            var vis = d3.select("body").append("svg:svg")
                .attr("width", w + m[1] + m[3])
                .attr("height", h + m[0] + m[2])
                .append("svg:g")
                .attr("transform", "translate(" + m[3] + "," + m[0] + ")")
                .call(zoom);
            
            //ajout pour trapper les évènements correctement
            var rect = vis.append("rect")
                .attr("width", w)
                .attr("height", h)
                .style("fill", "none")
                .style("pointer-events", "all");
            
            
            //lecture des données
            d3.json("json_kinship.php", function(json) {
              root = json;
              root.x0 = h / 2;
              root.y0 = 0;
            
              update(root);
            });
            
            
            //fonction permettant de mettre à jour l'affichage de l'arbre
            function update(source) {
              var duration = d3.event && d3.event.altKey ? 5000 : 500;
            
              // Compute the new tree layout.
              var nodes = tree.nodes(root).reverse();
            
              // Normalize for fixed-depth.
              nodes.forEach(function(d) { d.y = d.depth * 100; });
            
              // Update the nodes…
              var node = vis.selectAll("g.node")
                  .data(nodes, function(d) { return d.id || (d.id = ++i); });
            
              // Enter any new nodes at the parent's previous position.
              var nodeEnter = node.enter().append("svg:g")
                  .attr("class", "node")
                  .attr("transform", function(d) { return "translate(" + source.x0*mult_x+ "," + source.y0 + ")"; })
                  .on("click", function(d) { toggle(d); update(d); });
            
              nodeEnter.append("svg:circle")
                  .attr("r", 1e-6)
                  .style("fill", function(d) { return d._children ? "#00C2FF" : "#fff"; });
            
              nodeEnter.append("svg:text")
                  .attr("x", 10)
                  .attr("dy", ".35em")
                  .attr("text-anchor", "start")
                  .text(function(d) { return d.name; })
                  .attr("transform", function(d) { return "rotate(-15 0 0)"; })
                  .style("fill-opacity", 1e-6);
            
              // Transition nodes to their new position.
              var nodeUpdate = node.transition()
                  .duration(duration)
                  .attr("transform", function(d) { return "translate(" + d.x*mult_x + "," + d.y + ")"; });
            
              nodeUpdate.select("circle")
                  //.attr("r", function(d){return 2.5 + 6*calculateNumberOfDescendants(d)/nodes.length ;})
                  .attr("r", function(d){return 4.5;})
                  .style("fill", function(d) { return d._children ? "#00C2FF" : "#fff"; });
            
              nodeUpdate.select("text")
                  .style("fill-opacity", 1);
            
              // Transition exiting nodes to the parent's new position.
              var nodeExit = node.exit().transition()
                  .duration(duration)
                  .attr("transform", function(d) { return "translate(" + source.x*mult_x + "," + source.y + ")"; })
                  .remove();
            
              nodeExit.select("circle")
                  .attr("r", 1e-6);
            
              nodeExit.select("text")
                  .style("fill-opacity", 1e-6);
            
              // Update the links…
              var link = vis.selectAll("path.link")
                  .data(tree.links(nodes), function(d) { return d.target.id; });
            
              // Enter any new links at the parent's previous position.
              link.enter().insert("svg:path", "g")
                  .attr("class", "link")
                  .attr("d", function(d) {
                    var o = {x: source.x0, y: source.y0};
                    return diagonal({source: o, target: o});
                  })
                .transition()
                  .duration(duration)
                  .attr("d", diagonal);
            
              // Transition links to their new position.
              link.transition()
                  .duration(duration)
                  .attr("d", diagonal);
            
              // Transition exiting nodes to the parent's new position.
              link.exit().transition()
                  .duration(duration)
                  .attr("d", function(d) {
                    var o = {x: source.x, y: source.y};
                    return diagonal({source: o, target: o});
                  })
                  .remove();
            
              // Stash the old positions for transition.
              nodes.forEach(function(d) {
                d.x0 = d.x;
                d.y0 = d.y;
              });
}


    </script>
        
    </body>
</html>