<?php get_header(); ?>

	<div id="mid" class="index">
    
    <div class="coda-nav-left" id="coda-nav-left-1"><a href="#" title="Slide left"><img src="<?php bloginfo('template_directory'); ?>/images/left.png" alt="Left" /></a></div>
	<div class="coda-nav-right" id="coda-nav-right-1"><a href="#" title="Slide right"><img src="<?php bloginfo('template_directory'); ?>/images/right.png" alt="Right" /></a></div>

		<!-- Start slider -->
		<div class="coda-slider-wrapper">

			<div class="coda-slider preload" id="coda-slider-1">
			<?php query_posts(array('orderby' => 'rand', 'category_name' => featured, 'showposts' => 5)); if (have_posts()) : while (have_posts()) : the_post(); ?>
			
				<div class="panel" id="post-<?php the_ID(); ?>" title="<?php the_title() ?>">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="Click to view <?php the_title_attribute(); ?>">
                    <div class="panel-wrapper">
							   <?php
							$media_type = get_post_meta($post->ID, 'lead_type', true); 
							$media = get_post_meta($post->ID, 'lead_image', true);
							?>
								<img src="<?php echo $media; ?>" alt="" width="940" height="600" />
						
						<div class="post-title">
							<?php the_title(); ?>
						</div>
						<div class="entry">
							<?php the_excerpt(); ?>
						</div> <!-- .entry -->
					</div><!-- .panel-wrapper -->
                    </a>
				</div><!-- .panel -->
				
				<?php endwhile; ?>
	
			<?php else : ?>
		
			<?php endif; ?>
	
			</div><!-- .coda-slider preload -->    
    	</div><!--.coda-slider-wrapper -->
        
                                <script type="text/javascript"> 
        jQuery(document).ready(function($){
                                $().ready(function() {
                                        $('#coda-slider-1').codaSlider({
											autoSlide:true,
											autoSlideInterval: 8000,
											autoSlideStopWhenClicked: true,
											dynamicArrows: false,
											dynamicTabs: true,
											dynamicTabsPosition: "bottom",
											});
                                });
        });
                        </script> 
                        
                        

	</div><!-- .mid -->	

<?php get_footer(); ?>