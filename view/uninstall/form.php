<?php
$deactivate_reasons = [
   'no_longer_needed' => [
      'title' => esc_html__('I no longer need the plugin', 'wp-access-manager'),
      'input' => '',
   ],
   'found_a_better' => [
      'title' => esc_html__('I found a better plugin', 'wp-access-manager'),
      'input' => esc_html__('Please share which plugin', 'wp-access-manager'),
   ],
   'couldnt_get_to_work' => [
      'title' => esc_html__('I couldn\'t get the plugin to work', 'wp-access-manager'),
      'input' => '',
   ],
   'temporary_deactivation' => [
      'title' => esc_html__('It\'s a temporary deactivation', 'wp-access-manager'),
      'input' => '',
   ],
   'has_pro' => [
      'title' => esc_html__('I have WP Access Manager Pro', 'wp-access-manager'),
      'input' => '',
      'alert' => esc_html__('Wait! Don\'t deactivate WP Access Manager. You have to activate both WP Access Manager and WP Access Manager Pro in order for the plugin to work.', 'wp-access-manager'),
   ],
   'other' => [
      'title' => esc_html__('Other', 'wp-access-manager'),
      'input' => esc_html__('Please share the reason', 'wp-access-manager'),
   ],
];

?>
<div class="wam">
   <div class="wam-overlay wam-feedback-modal" style="display: none">
      <div class="wam-modal-content">
         <div class="wam-modal-header wam-gradient">
               <span class="wam-close">
                  <svg
                     width="25"
                     height="25"
                     viewBox="0 0 16 16"
                     fill="none"
                  >
                     <path
                        d="M12.5 3.5L3.5 12.5"
                        stroke="#718096"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                     />
                     <path
                        d="M12.5 12.5L3.5 3.5"
                        stroke="#718096"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                     />
                  </svg>
               </span>
               <h2 class="wam-modal-title"><?php echo esc_html__('Quick Feedback', 'elementor'); ?></h2>
               <p><?php echo esc_html__('If you have a moment, please share why you are deactivating WP Access Manager', 'wp-access-manager'); ?></p>
         </div>

         <form>
            <div class="wam-content">
               <div class="wam-form-style-one">
                  <?php wp_nonce_field('_wam_deactivate_nonce'); ?>
                  <div class="row">
                     <?php foreach ($deactivate_reasons as $reason_key => $reason) : ?>
                        <div class="col-12">
                           <div class="wam-field-radio">
                              <input
                                 type="radio"
                                 id="wam-deactivate-<?php echo esc_attr($reason_key); ?>"
                                 class="wam-deactivate-reason"
                                 name="reason_key"
                                 value="<?php echo esc_attr($reason_key); ?>"
                              />
                              <label for="wam-deactivate-<?php echo esc_attr($reason_key); ?>"><?php echo esc_html($reason['title']); ?></label> 
                           </div>

                           <?php if (! empty($reason['input'])) : ?>
                              <div class="wam-feedback-text" style="display: none" >
                                 <input
                                    type="text"
                                    name="reason_<?php echo esc_attr($reason_key); ?>"
                                    placeholder="<?php echo esc_attr($reason['input']); ?>"
                                 />
                              </div>
                           <?php endif; ?>

                           <?php if (! empty($reason['alert'])) : ?>
                              <div class="wam-feedback-alert" style="display: none; color: #ff0000"><?php echo esc_html($reason['alert']); ?></div>
                           <?php endif; ?>
                        </div>
                     <?php endforeach; ?>

                     <div class="wam-data-alert" style="display: none; margin-top: 10px">
                        <div class="col-12">
                           <div class="wam-field-radio">
                              <input
                                 type="checkbox"
                                 id="wam-data-collect"
                                 name="data_collect"
                                 value='1'
                                 style="zoom: 1"
                                 checked
                              />
                              <label for="wam-data-collect" style="font-size: 11px"><?php _e('Share your Name and Email for communication purposes', 'wp-access-manager'); ?></label>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="wam-modal-footer wam-mt-25">
               <div class="row">
                  <div class="col">
                     <button class="wam-feedback-skip wam-btn wam-text-hover-blue"><?php _e('Skip & Deactivate', 'wp-access-manager'); ?></button>
                  </div>
                  <div class="col">
                     <button class="wam-feedback-submit wam-btn wam-bg-blue wam-bg-hover-blue wam-btn-big wam-float-right wam-color-white">
                        <?php _e('Submit & Deactivate', 'wp-access-manager'); ?>
                     </button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<?php