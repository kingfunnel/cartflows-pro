<?php
// @codingStandardsIgnoreStart

$image_available = !empty( $bump_order_image ) ? true : false;

?>
<div class="wcf-bump-order-wrap wcf-bump-order-<?php echo $ob_id; ?> wcf-bump-order-style-5 wcf-<?php echo $position; ?> wcf-ob-column-<?php echo $bump_layout_width; ?>">
	
	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<!-- wcf-bump-order-content -->
	<div class="wcf-bump-order-content">
		<!-- wcf-bump-order-field-wrap -->
		<div class="wcf-bump-order-field-wrap">
			
			<div class="wcf-bump-order-info wcf-bump-order-image-<?php echo $bump_image_position; ?>">
			
				<?php 
					if( !empty( $bump_image_position ) && 'right' === $bump_image_position ){
				?>	
					<!-- Order bump description & action section -->
					<div class="wcf-bump-order-text">
						<div class="wcf-bump-order-label">
					<?php if( !empty( $order_bump_title ) ){ ?>
								<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
							<?php }?>
						</div>

				<?php if( !empty( $order_bump_desc ) ) { ?>
							<div class="wcf-bump-order-desc"><?php echo wp_kses_post( $order_bump_desc ); ?></div>
						<?php } ?>

				<?php if ( 'checkbox' === $order_bump_action_element ) { ?>
							<div class="wcf-bump-order-action">
								<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
								<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb" name="wcf-bump-order-cb" value="<?php echo $product_id ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
								<?php if( !empty( $order_bump_label ) ){ ?>
										<label><?php echo esc_html( $order_bump_label ); ?></label>
								<?php }?>
							</div>
						<?php } ?>
						
				<?php if ( 'button' === $order_bump_action_element ) { ?>
							<div class="wcf-bump-order-action wcf-ob-action-button">
								<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb" name="wcf-bump-order-cb" value="<?php echo $product_id ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
								<a class="wcf-bump-order-cb-button wcf-bump-add-to-cart"><?php echo esc_html__( 'Add', 'cartflows-pro' ); ?></a>
							</div>
						<?php } ?>

					</div>

				<?php if( !empty( $bump_order_image ) ){ ?>
						<!-- Order bump image section -->
						<div class="wcf-bump-order-image">
							<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" alt='' class="wcf-image" />
						</div>
					<?php } ?>
				<?php
					}else{
				
				?>
				<?php if( !empty( $bump_order_image ) ){ ?>
					<!-- Order bump image section -->
					<div class="wcf-bump-order-image">
						<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" class="wcf-image" />
					</div>
				<?php } ?>
				
				<!-- Order bump description & action section -->
				<div class="wcf-bump-order-text">
					<div class="wcf-bump-order-label">
				<?php if( !empty( $order_bump_title ) ){ ?>
							<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
						<?php }?>
					</div>

				<?php if( !empty( $order_bump_desc ) ) { ?>
						<div class="wcf-bump-order-desc"><?php echo wp_kses_post( $order_bump_desc ); ?></div>
					<?php } ?>

				<?php if ( 'checkbox' === $order_bump_action_element ) { ?>
						<div class="wcf-bump-order-action">
							<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
							<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb" name="wcf-bump-order-cb" value="<?php echo $product_id ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
							<?php if( !empty( $order_bump_label ) ){ ?>
									<label><?php echo esc_html( $order_bump_label ); ?></label>
							<?php }?>
						</div>
					<?php } ?>
					
				<?php if ( 'button' === $order_bump_action_element ) { ?>
						<div class="wcf-bump-order-action wcf-ob-action-button">
							<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb" name="wcf-bump-order-cb" value="<?php echo $product_id ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
							<a class="wcf-bump-order-cb-button wcf-bump-add-to-cart"><?php echo esc_html__( 'Add', 'cartflows-pro' ); ?></a>
						</div>
					<?php } ?>

				</div>
			<?php
				}
			?>
				
			</div>
			
		</div>
		
	</div>
</div> 
 <!-- Main Div Close -->
<?php 
	// @codingStandardsIgnoreEnd
