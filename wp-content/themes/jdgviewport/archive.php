<?php
/*
Template Name: Archive
*/
?>

<?php get_header(); ?>

<?php global $query_string; ?>
<?php query_posts( $query_string . '&cat=5' ); ?>


<div id="mid" class="archive">

<?php if ($wp_query->found_posts >8) {?>
    <div class="coda-nav-left" id="coda-nav-left-1"><a href="#" title="Slide left"><img src="<?php bloginfo('template_directory'); ?>/images/left.png" alt="Left" /></a></div>
	<div class="coda-nav-right" id="coda-nav-right-1"><a href="#" title="Slide right"><img src="<?php bloginfo('template_directory'); ?>/images/right.png" alt="Right" /></a></div>
<?php }?>

		<!-- Start slider -->
		<div class="coda-slider-wrapper">
			<div class="coda-slider preload" id="coda-slider-1">
			
			<?php $c=0; ?>
		            
			 <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<?php if ($c==0) : ?>
				<div class="panel">
				<?php endif; ?>
				
				<div class="wrapper-archive">
					<?php $image = get_post_meta($post->ID, 'archive_image', true); if ($image=="") { $image = get_post_meta($post->ID, 'lead_image', true); } ?>
					<img src="<?php echo $image; ?>" alt="" width="175" height="230" />
				
                	<div class="post-title">
               		<a href="<?php the_permalink() ?>" rel="bookmark" title="Click to view <?php the_title_attribute(); ?>"><span><?php the_title(); ?></span></a>
					</div> <!--.post-title -->
                    
                </div> <!-- .wrapper-archive -->
                
				<?php $c++; if ($c==8){ $c=0; ?>
                </div> <!-- .panel -->
				<?php } endwhile; ?>
				<?php if ($c==0) { ?>
                <div class="panel">
				<?php	} ?>
						<div class="more-button">
							<?php previous_posts_link('&laquo; Newer Projects') ?>
							<?php next_posts_link('Older Projects &raquo;') ?>
						</div> <!-- .more-button -->
                </div> <!-- .panel -->

			<?php else : ?>
		
			<?php endif; ?>
	
			</div><!-- .coda-slider preload -->    
		</div><!--.coda-slider-wrapper -->
		

                                <script type="text/javascript"> 
        jQuery(document).ready(function($){
                                $().ready(function() {
                                        $('#coda-slider-1').codaSlider({
											dynamicArrows: false,
											dynamicTabs: true,
											dynamicTabsPosition: "bottom",
											});
                                });
        });
                        </script> 


</div><!-- .mid -->
	
    <div id="archive-filter">
    <ul class="nav">
	<li class="left"></li>
	<?php wp_nav_menu( array( 'container_class' => 'menu-archfilt', 'theme_location' => 'archive-filter-menu' ) ); ?>
	<li class="right"></li>
    </ul>
    <?php
    global $wp_query;
    $total_results = $wp_query->found_posts;
    echo nl2br("\n\n\n");echo $total_results; echo nl2br(" Projects Found")
    ?>
	</div>
    



<?php get_footer(); ?>

