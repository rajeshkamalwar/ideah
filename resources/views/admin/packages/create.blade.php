 <div class="modal fade" id="createModal" tabindex="-1" role="dialog" arititletotala-labelledby="exampleModalCenterTitle"
   aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Package') }}</h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">

         <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
           action="{{ route('admin.package.store') }}" method="POST">
           @csrf
           <div class="form-group">
             <label for="title">{{ __('Package title') . '*' }}</label>
             <input id="title" type="text" class="form-control" name="title"
               placeholder="{{ __('Enter Package title') }}" value="">
             <p id="err_title" class="mt-2 mb-0 text-danger em"></p>
           </div>
           <div class="form-group">
             <label for="price">{{ __('Price') }} ({{ $settings->base_currency_text }})*</label>
             <input id="price" type="number" class="form-control" name="price"
               placeholder="{{ __('Enter Package price') }}" value="">
             <p class="text-warning">
               <small>{{ __('If price is 0 , than it will appear as free') }}</small>
             </p>
             <p id="err_price" class="mt-2 mb-0 text-danger em"></p>
           </div>

           <div class="form-group">
             <label for="">{{ __('Icon') . '*' }}</label>
             <div class="btn-group d-block">
               <button type="button" class="btn btn-primary iconpicker-component">
                 <i class="fas fa-gift"></i>
               </button>
               <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle" data-selected="fa-car"
                 data-toggle="dropdown"></button>
               <div class="dropdown-menu"></div>
             </div>
             <input type="hidden" id="inputIcon" name="icon">
             <p id="err_icon" class="mt-2 mb-0 text-danger em"></p>
           </div>

           <div class="form-group">
             <label for="term">{{ __('Package term') . '*' }}</label>
             <select id="term" name="term" class="form-control" required>
               <option value="" selected disabled>{{ __('Choose a Package term') }}</option>
               <option value="monthly">{{ __('Monthly') }}</option>
               <option value="yearly">{{ __('Yearly') }}</option>
               <option value="lifetime">{{ __('Lifetime') }}</option>
             </select>
             <p id="err_term" class="mb-0 text-danger em"></p>
           </div>


           <div class="form-group">
             <label class="form-label">{{ __('Package Features') }}</label>
             <div class="selectgroup selectgroup-pills">
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Listing Enquiry Form" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Listing Enquiry Form') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Video" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Video') }}</span>
               </label>

               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Amenities" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Amenities') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Feature" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Features') }}</span>
               </label>

               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Social Links" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Social Links') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="FAQ" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('FAQ') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Business Hours" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Business Hours') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Products" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Products') }}</span>
               </label>
               <label class="selectgroup-item" id="productEnquiryFormLabel">
                 <input type="checkbox" name="features[]" value="Product Enquiry Form" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Product Enquiry Form') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Messenger" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Messenger') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="WhatsApp" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('WhatsApp') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Telegram" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Telegram') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="checkbox" name="features[]" value="Tawk.To" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Tawk.To') }}</span>
               </label>

             </div>
             <p id="err_features" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group">
             <label class="form-label">{{ __('Number of Listings') . '*' }} </label>
             <input type="number" class="form-control" name="number_of_listing"
               placeholder="{{ __('Enter Number of Listings') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_listing" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group">
             <label class="form-label">{{ __('Number of image per Listing') . '*' }}</label>
             <input type="number" class="form-control" name="number_of_images_per_listing"
               placeholder="{{ __('Enter Number of image per Listing') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_images_per_listing" class="mb-0 text-danger em"></p>
           </div>

           <div class="form-group amenities-box">
             <label for="">{{ __('Number of Amenities per Listing') . '*' }} </label>
             <input type="number" class="form-control" name="number_of_amenities_per_listing"
               placeholder="{{ __('Enter Number of Amenities per Listing') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_amenities_per_listing" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group additional-specification-box">
             <label for="">{{ __('Number of Features per Listing') . '*' }} </label>
             <input type="number" class="form-control" name="number_of_additional_specification"
               placeholder="{{ __('Enter Number of Features per Listing') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_additional_specification" class="mb-0 text-danger em"></p>
           </div>

           <div class="form-group social-links-box vcrd-none">
             <label for="">{{ __('Number of Social Links per Listing') . '*' }} </label>
             <input type="number" class="form-control" name="number_of_social_links"
               placeholder="{{ __('Enter Number of Social Links per Listing') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_social_links" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group FAQ-box">
             <label for="">{{ __('Number of FAQs per Listing') . '*' }}</label>
             <input type="number" class="form-control" name="number_of_faq"
               placeholder="{{ __('Enter Number of FAQs per Listing') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_faq" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group Products-box">
             <label for="">{{ __('Number of Products') . '*' }} </label>
             <input type="number" class="form-control" name="number_of_products"
               placeholder="{{ __('Enter Number of Products') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_products" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group image-product-box">
             <label for="">{{ __('Number of Images per Product') . '*' }} </label>
             <input type="number" class="form-control" name="number_of_images_per_products"
               placeholder="{{ __('Enter Number of Images per Product') }}">
             <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
             <p id="err_number_of_images_per_products" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group">
             <label for="status">{{ __('Status') . '*' }}</label>
             <select id="status" class="form-control ltr" name="status">
               <option value="" selected disabled>{{ __('Select a status') }}</option>
               <option value="1">{{ __('Active') }}</option>
               <option value="0">{{ __('Deactive') }}</option>
             </select>
             <p id="err_status" class="mb-0 text-danger em"></p>
           </div>
           <div class="form-group">
             <label class="form-label">{{ __('Popular') }}</label>
             <div class="selectgroup w-100">
               <label class="selectgroup-item">
                 <input type="radio" name="recommended" value="1" class="selectgroup-input">
                 <span class="selectgroup-button">{{ __('Yes') }}</span>
               </label>
               <label class="selectgroup-item">
                 <input type="radio" name="recommended" value="0" class="selectgroup-input" checked>
                 <span class="selectgroup-button">{{ __('No') }}</span>
               </label>
             </div>
           </div>


           <div class="form-group">
             <label>{{ __('Custom Features') }}</label>
             <textarea class="form-control" name="custom_features" rows="5"
               placeholder="{{ __('Enter Custom Features') }}"></textarea>
             <p class="text-warning">
               <small>{{ __('Enter new line to seperate features') }}</small>
             </p>
           </div>


         </form>
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
         <button id="submitBtn" type="button" class="btn btn-primary">{{ __('Submit') }}</button>
       </div>
     </div>
   </div>
 </div>
