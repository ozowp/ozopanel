<?php
$deactivate_reasons = [
   'no_longer_needed' => [
      'title' => esc_html__('I no longer need the plugin', 'ozopanel'),
      'input' => '',
   ],
   'found_a_better' => [
      'title' => esc_html__('I found a better plugin', 'ozopanel'),
      'input' => esc_html__('Please share which plugin', 'ozopanel'),
   ],
   'couldnt_get_to_work' => [
      'title' => esc_html__('I couldn\'t get the plugin to work', 'ozopanel'),
      'input' => '',
   ],
   'temporary_deactivation' => [
      'title' => esc_html__('It\'s a temporary deactivation', 'ozopanel'),
      'input' => '',
   ],
   'has_pro' => [
      'title' => esc_html__('I have OzoPanel Pro', 'ozopanel'),
      'input' => '',
      'alert' => esc_html__('Wait! Don\'t deactivate OzoPanel. You have to activate both OzoPanel and OzoPanel Pro in order for the plugin to work.', 'ozopanel'),
   ],
   'other' => [
      'title' => esc_html__('Other', 'ozopanel'),
      'input' => esc_html__('Please share the reason', 'ozopanel'),
   ],
];

?>
<div class="ozopanel">
   <div class="ozopanel-overlay ozopanel-feedback-modal" style="display: none">
      <div class="ozopanel-modal-content">
         <div class="ozopanel-modal-header ozopanel-gradient">
               <span class="ozopanel-close">
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
               <h2 class="ozopanel-modal-title"><?php echo esc_html__('Quick Feedback', 'ozopanel'); ?></h2>
               <p><?php echo esc_html__('If you have a moment, please share why you are deactivating OzoPanel', 'ozopanel'); ?></p>
         </div>

         <form>
            <div class="ozopanel-content">
               <div class="ozopanel-form-style-one">
                  <?php wp_nonce_field('_ozopanel_deactivate_nonce'); ?>
                  <div class="row">
                     <?php foreach ($deactivate_reasons as $reason_key => $reason) : ?>
                        <div class="col-12">
                           <div class="ozopanel-field-radio">
                              <input
                                 type="radio"
                                 id="ozopanel-deactivate-<?php echo esc_attr($reason_key); ?>"
                                 class="ozopanel-deactivate-reason"
                                 name="reason_key"
                                 value="<?php echo esc_attr($reason_key); ?>"
                              />
                              <label for="ozopanel-deactivate-<?php echo esc_attr($reason_key); ?>"><?php echo esc_html($reason['title']); ?></label>
                           </div>

                           <?php if (! empty($reason['input'])) : ?>
                              <div class="ozopanel-feedback-text" style="display: none" >
                                 <input
                                    type="text"
                                    name="reason_<?php echo esc_attr($reason_key); ?>"
                                    placeholder="<?php echo esc_attr($reason['input']); ?>"
                                 />
                              </div>
                           <?php endif; ?>

                           <?php if (! empty($reason['alert'])) : ?>
                              <div class="ozopanel-feedback-alert" style="display: none; color: #ff0000"><?php echo esc_html($reason['alert']); ?></div>
                           <?php endif; ?>
                        </div>
                     <?php endforeach; ?>

                     <div class="ozopanel-data-alert" style="display: none; margin-top: 10px">
                        <div class="col-12">
                           <div class="ozopanel-field-radio">
                              <input
                                 type="checkbox"
                                 id="ozopanel-data-collect"
                                 name="data_collect"
                                 value='1'
                                 style="zoom: 1"
                                 checked
                              />
                              <label for="ozopanel-data-collect" style="font-size: 11px"><?php esc_html_e('Share your Name and Email for communication purposes', 'ozopanel'); ?></label>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="ozopanel-modal-footer ozopanel-mt-25">
               <div class="row">
                  <div class="col">
                     <button class="ozopanel-feedback-skip ozopanel-btn ozopanel-text-hover-blue"><?php esc_html_e('Skip and Deactivate', 'ozopanel'); ?></button>
                  </div>
                  <div class="col">
                     <button class="ozopanel-feedback-submit ozopanel-btn ozopanel-bg-blue ozopanel-bg-hover-blue ozopanel-btn-big ozopanel-float-right ozopanel-color-white">
                        <?php esc_html_e('Submit and Deactivate', 'ozopanel'); ?>
                     </button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<?php