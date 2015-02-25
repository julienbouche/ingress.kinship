{
        "name": "?",
        "size": "",
        "children": [
<?php

    require_once("include/db.php");
    
    
    db_connect();
    //on sélectionne tous ceux qui n'ont pas de père
    
    $sql = "SELECT name, id_user FROM USER WHERE id_parent IS NULL";
    $result = mysql_query($sql);
    
    if($result && mysql_num_rows($result)>0){
        $nb_player = mysql_num_rows($result);
        $cpt=1;
        while($row = mysql_fetch_assoc($result)){
            
?>
    {
        "name": "@<?=$row['name']?>",
        "size": ""
        <?php getChildren($row['id_user']); ?>
    }

<?php
            if($cpt < $nb_player){
                echo ",";
            }
            $cpt++;
        }
    }
?>
]}


<?php
    function getChildren($userid){
        $sql = "SELECT name, id_user from USER WHERE id_parent=$userid";
        $result = mysql_query($sql);
        if($result && mysql_num_rows($result)){
            $nb_player = mysql_num_rows($result);
            $cpt=1;
            
            ?>
        ,"children" : [
<?php
            while($row = mysql_fetch_assoc($result)){
                
?>
        {
            "name": "@<?=$row['name']?>",
            "size": ""
            <?php getChildren($row['id_user']); ?>
        }
    
<?php
                if($cpt < $nb_player){
                    echo ",";
                }
                $cpt++;
            }
?>
        ]
<?php
        }
    }
?>