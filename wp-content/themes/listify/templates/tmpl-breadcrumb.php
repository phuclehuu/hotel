<?php
    $html = '';
    $printed = array();
    $categories = get_the_category();
    $path = $_SERVER['REQUEST_URI'];
    $count_path = substr_count($path, '/');

    if( count( $categories ) == 1) {
        echo breadcrumb_get_parent_category( $categories[0], $html );
    }
    else{
        foreach( $categories as $category){
            if( $category->category_parent != 0 && !in_array($category->category_parent, $printed )){
                echo breadcrumb_print_category( get_category( $category->category_parent ) );
                $printed[] = $category->category_parent;
            }
            if( !in_array($category->cat_ID, $printed ) && ($count_path > 1) ){
                echo breadcrumb_print_category( get_category( $category ) );
                $printed[] = $category->cat_ID;
            }
            if(!in_array($category->cat_ID, $printed ) && is_single()){
                echo breadcrumb_print_category( get_category( $category ) );
                $printed[] = $category->cat_ID; 
            }
        }
    }
?>
<?php if(is_single()){ ?>
<li><strong><?php the_title(); ?></strong></li>
<?php }?>