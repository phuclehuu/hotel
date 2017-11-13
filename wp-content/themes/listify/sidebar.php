<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Listify
 */

if ( ! is_active_sidebar( 'widget-area-sidebar-1' ) ) {
	return;
}
?>

<div id="secondary" class="widget-area col-md-4 col-sm-5 col-xs-12" role="complementary">
	<?php // dynamic_sidebar( 'widget-area-sidebar-1' ); ?>

	<aside id="column_sidebar" class="widget widget_recent_entries">		
			<!-- <h3 class="widget-title">Recent Posts</h3> -->
			<!-- <div style="height: 250px;background: #ccc;position: relative">
                <div style="position: absolute;top:110px;right: 125px;">google ads</div>
            </div> -->

			<div class="title-sec">
	            <div class="tt-inner">
	              <h2>HOT NEWS</h2>
	              <p>Hot news in this week</p>
	            </div>
	        </div>		
			<ul class="side-list-art">
			    <ul class="wpp-list">
			        <?php
	                  //get list top 5 page view from gg analytic
	                  $ranking_cat_slug = "";
	                  if (is_single()){
	                      $current_post_id = get_the_ID();
	                      $cat_id = get_last_category_id($current_post_id);
	                      $category = get_category($cat_id);
	                      $ranking_cat_slug = "$category->slug";
	                      $idList = get_post_top_ranking_view(5, $cat_id, $current_post_id);
	                  }elseif (is_category()){
	                      $cat_id = get_queried_object()->term_id;
	                      $category = get_category($cat_id);
	                      $ranking_cat_slug = "$category->slug";
	                      $idList = get_post_top_ranking_view(5, $cat_id);
	                  }else{
	                      $idList = get_post_top_ranking_view(5);
	                  }

	                    if(!is_null($idList)){
	                      foreach ($idList as $id) {
	                        $article = get_post($id);
	                        $post_permalink = get_permalink( $id );
	                      ?>
	                      <li class="art-li-cont clearfix">
	                          <div class="art-thumb" >
	                            <a href="<?php echo($post_permalink); ?>" class="lazy">
	                                <?php echo get_the_post_thumbnail( $id, 'thumbnail' ); ?>
	                            </a>
	                          </div>
	                         <div class="art-text">
	                            <p class="side-art-title"><a href="<?php echo($post_permalink); ?>"><?php echo($article->post_title); ?></a></p>
	                            <p class="side-art-user">
	                                <?php
	                                  $id_author = $article->post_author;
	                                  $link_author = get_author_posts_url( $id_author );
	                                ?>
	                               <a href="<?php echo $link_author; ?>"><?php the_author_meta( 'user_login', $id_author ); ?></a>
	                            </p>
	                         </div>
	                      </li>
	                      <?php }//end for?>
	                    <?php }//end if?>
			    </ul>
			</ul>
			<p class="link">
                <?php
                if ($ranking_pages = get_ranking_pages()){
                    // $ranking_permalink = get_permalink($ranking_pages[0]).$ranking_cat_slug;
                    $ranking_permalink = get_site_url().'/ranking/'.$ranking_cat_slug;
                    ?>
                    <a href="<?php echo $ranking_permalink;?>">Read more ></a>
                    <?php
                }
                ?>
            </p>
            <div class="title-sec">
              <div class="tt-inner">
                <h2>SPECIAL NEWS</h2>
                <p>List posts EXUTRA</p>
              </div>
           </div>
           <ul class="side-list-art">
           		<?php 
                     $posts = get_posts(array(
                        'numberposts'   => 5,
                        'post_type'     => 'post',
                        'meta_query'    => array(
                            array(
                                'key'       => 'featured',
                                'value'     => true,
                                'compare'   => '=',
                            )
                        ),
                     ));
                 ?>
                <?php foreach($posts as $post): setup_postdata($post); ?>

                        <li class="art-li-cont clearfix">
                           <div class="art-thumb" >
                              <a href="<?php the_permalink(); ?>" class="lazy">
                                <?php the_post_thumbnail('square'); ?>
                              </a>
                           </div>
                           <div class="art-text">
                              <p class="side-art-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
                              <p class="side-art-user">
                                  <?php
                                  $id_author = get_the_author_meta( 'ID' );
                                  $author = get_the_author();
                                  $link_author = get_author_posts_url( $id_author );
                                  ?>
                                 <a href="<?php echo $link_author; ?>"><?php the_author(); ?></a>
                              </p>
                           </div>
                        </li>                   
                  <?php endforeach;
                  wp_reset_postdata();
                  ?>
                 <div class="title-sec">
                  <div class="tt-inner">
                    <h2>TOPIC BY TAGS</h2>
                    <p>Hot tags of EXUTRA</p>
                  </div>
               </div>
               <section class="side-tags"> 
                 <ul class="keyword-tags">
                    <?php
                        $args = array(
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 20
                        );
                        $tags = get_tags( $args );
                        //var_dump($tags);
                        if( count( $tags ) > 0 )
                        {
                            foreach( $tags as $tag ){
                                $tag_link = get_tag_link( $tag->term_id);
                                echo '<li class="tags">';
                                echo '<a href="' . $tag_link . '"> <i class="fa fa-tags" aria-hidden="true"></i>  ' . $tag->name . '</a>';
                                echo '</li>';
                            }
                        }
                    ?>                    

                 </ul> 
                 <!-- <p class="link-tags">»&nbsp;<a href="#">Liệt kê từ khoá</a></p> -->
                <!-- <p class="list-tag"><a href="/tags">Đến trang danh sách từ khóa ></a></p> -->
			    <div class="title-sec">
                  <div class="tt-inner">
                    <h2>FOLLOW EXUTRA ON Facebook</h2>
                  </div>
              	</div>
	            <div class="sidebar_fb">
	                <iframe src="https://www.facebook.com/plugins/page.php?href=https://www.facebook.com/Exutra-1520375851382052/&tabs=timeline&width=340&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true&appId=438398986209291" width="340" height="500" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
	             </div>
			</ul>
	</aside>
</div><!-- #secondary -->
