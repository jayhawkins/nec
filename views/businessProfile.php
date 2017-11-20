
<ol class="breadcrumb">
    <li>ADMIN</li>
    <li class="active">Business Maintenance</li>
</ol>
<div class="row">
    <div class="col-lg-8 col-sm-offset-2">
        <section class="widget">
            <div class="widget-body">
              <form id="formRegister" class="register-form mt-lg">
                <input type="hidden" id="entityID" name="entityID" value="" />
                <input type="hidden" id="locationID" name="locationID" value="" />
                <input type="hidden" id="contactID" name="contactID" value="" />
                <div class="row">
                    <div class="col-sm-4">
                        <label for="firstName">First Name</label>
                        <div class="form-group">
                          <input type="text" class="form-control" id="firstName" name="firstName" placeholder="*First Name" value="" required="required" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="lastName">Last Name</label>
                        <div class="form-group">
                          <input type="text" id="lastName" name="lastName" class="form-control" placeholder="*Last Name" value="" required="required" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                      <label for="title">Title</label>
                      <div class="form-group">
                        <input type="text" id="title" name="title" class="form-control" placeholder="Title" value="" />
                      </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-8">
                        <label for="entityName">Company Name</label>
                        <div class="form-group">
                          <input type="text" id="entityName" name="entityName" class="form-control" placeholder="*Company Name" value="" required="required" />
                        </div>
                    </div>
                    <div class="col-sm-2">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="address1">Address 1</label>
                        <div class="form-group">
                          <input type="text" id="address1" name="address1" class="form-control mb-sm" placeholder="Company Address" value="" />
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="address2">Suite # / Apt #</label>
                        <div class="form-group">
                          <input type="text" id="address2" name="address2" class="form-control mb-sm" placeholder="Bldg. Number/Suite" value="" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label for="city">City</label>
                        <div class="form-group">
                          <input type="text" id="city" name="city" class="form-control" placeholder="*City" value="" required="required" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="state">State</label>
                        <div class="form-group">
                          <select id="state" name="state" data-placeholder="State" class="form-control chzn-select" data-ui-jq="select2" required="required">
                            <option value="">*Select State...</option>
                          </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="zip">Zip</label>
                        <div class="form-group">
                          <input type="text" id="zip" name="zip" class="form-control mb-sm" placeholder="Zip" value="" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="phone">Phone</label>
                        <div class="form-group">
                           <div class="col-sm-7" style="padding-left: 0; padding-right: 0">
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="*Phone" value="" required="required" />
                           </div>
                           <div class="col-sm-5" style="padding-right: 0;">
                              <input type="text" maxlength="15" id="phoneExt" name="phoneExt" class="form-control" placeholder="Ext" />
                           </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="fax">Fax</label>
                        <div class="form-group">
                            <input type="text" id="fax" name="fax" class="form-control" placeholder="Fax" value="" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <label for="email">Email Address</label>
                        <div class="form-group">
                          <input type="email" id="email" name="email" class="form-control" placeholder="*Email Address" value="" required="required" />
                        </div>
                    </div>
				</div>
                <div class="clearfix">
                    <div class="btn-toolbar pull-left">
                      &nbsp;
                    </div>
                    <div>&nbsp;</div>
                    <div class="btn-toolbar pull-right">
                      <input type="submit" id="register" name="register" class="btn btn-inverse btn-sm" value="Save" />
                    </div>
                </div>
              </form>
            </div>
        </section>
    </div>
</div>

 <script>

 function handleError(property, selector, data) {
	 addError(property, selector, data);
	 removeError(selector);
 }

 function removeError(selector) {
		if ($('#' + selector + '-error').length > 0) {
			$('#' + selector + '-error').remove();
			$('#' + selector).removeClass('form-control-danger');
			$('#' + selector).parent().removeClass('has-danger');
		}
 }

 function addError(property, selector, data) {
	if (data.results.hasOwnProperty(property)) {
		if ($('#' + selector + '-error').length == 0) {
			$('#' + selector).addClass('form-control-danger').after('<div id="' + selector + '-error" class="form-control-feedback">' + data['results'][property] + '</div>');
			$('#' + selector).parent().addClass('has-danger');
		}
	}
 }

 $(function() {

	/* load content */
    var profileBusinessInfoXhr = $.getJSON('/profiles/business/info', function(data) {

		if (data.hasOwnProperty('status') && data.hasOwnProperty('results')) {

			if (data.status === 'success') {

				if (data.results.hasOwnProperty('id')) {
					$('#entityID').val(data.results.id);
				}

				if (data.results.hasOwnProperty('locationID')) {
					$('#locationID').val(data.results.locationID);
				}

				if (data.results.hasOwnProperty('contactID')) {
					$('#contactID').val(data.results.contactID);
				}

				if (data.results.hasOwnProperty('entityName')) {
					$('#entityName').val(data.results.entityName);
				}

				if (data.results.hasOwnProperty('title')) {
					$('#title').val(data.results.title);
				}

				if (data.results.hasOwnProperty('firstName')) {
					$('#firstName').val(data.results.firstName);
				}

				if (data.results.hasOwnProperty('lastName')) {
					$('#lastName').val(data.results.lastName);
				}

				if (data.results.hasOwnProperty('address1')) {
					$('#address1').val(data.results.address1);
				}

				if (data.results.hasOwnProperty('address2')) {
					$('#address2').val(data.results.address2);
				}

				if (data.results.hasOwnProperty('city')) {
					$('#city').val(data.results.city);
				}

				if (data.results.hasOwnProperty('zip')) {
					$('#zip').val(data.results.zip);
				}

				if (data.results.hasOwnProperty('emailAddress')) {
					$('#email').val(data.results.emailAddress);
				}

				if (data.results.hasOwnProperty('primaryPhone')) {

		    			var phoneindex = data.results.primaryPhone.indexOf(' x');
		            if (phoneindex != -1) {
		           	 	var phone  = data.results.primaryPhone.substring(0, phoneindex);
		           	 	var phoneExt = data.results.primaryPhone.substring(phoneindex + 2, data.results.primaryPhone.length);
		           	 	$('#phone').val(phone);
		            		$('#phoneExt').val(phoneExt);
		            } else {
		                $('#phone').val(data.results.primaryPhone);
		                $('#phoneExt').val('');
		            }

				}

				if (data.results.hasOwnProperty('fax')) {
					$('#fax').val(data.results.fax);
				}

				if (data.results.hasOwnProperty('states') && data.results.hasOwnProperty('state')) {
					var state = $('#state');
					$.each(data.results.states, function(key, val) {

						if (val[0] === data.results.state) {
							state.append($("<option />").attr({ 'selected':'selected' }).val(val[0]).text(val[1]));
						} else {
							state.append($("<option />").val(val[0]).text(val[1]));
						}

					});
				}

			}
		}

    });

    $('#register').click(function(event) {

    		event.preventDefault();

		var phone = $('#phone').val().replace(/(\d{3})\-?(\d{3})\-?(\d{4})/, '$1-$2-$3');
		var phoneExt = $('#phoneExt').val();
		if (phoneExt != "") {
			phone = phone + " x" + phoneExt;
		}

        var json = {
                    'id' : $('#entityID').val(),
                    'locationID' : $('#locationID').val(),
                    'contactID' : $('#contactID').val(),
                    'entityName' : $('#entityName').val(),
                    'address1' : $('#address1').val(),
                    'address2' : $('#address2').val(),
                    'city' : $('#city').val(),
                    'state' : $('#state').val(),
                    'zip' : $('#zip').val(),
                    'firstName' : $('#firstName').val(),
                    'lastName' : $('#lastName').val(),
                    'title' : $('#title').val(),
                    'emailAddress' : $('#email').val(),
                    'primaryPhone' : phone,
                    'fax' : $('#fax').val()
                };

        	$.ajax({
        	    type: 'POST',
        	    contentType: 'application/json; charset=utf-8',
        	    url: '/profiles/business/info',
        	    data: JSON.stringify(json),
        	    dataType: "json",
        	    success: function(data) {

        			if (data.hasOwnProperty('status') && data.hasOwnProperty('results')) {

        				if (data.status == 'fail') {

                			if ($('#successMessage').length > 0) {
                				$('#successMessage').remove();
                			}

           				if (data.results.hasOwnProperty('entityName')) {
            					if ($('#entityName-error').length == 0) {
            						$('#entityName').addClass('form-control-danger').after('<div id="entityName-error" class="form-control-feedback">' + data.results.entityName + '</div>');
            						$('#entityName').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#entityName-error').length > 0) {
            						$('#entityName-error').remove();
            						$('#entityName').removeClass('form-control-danger');
            						$('#entityName').parent().removeClass('has-danger');
            					}
        					}

           				if (data.results.hasOwnProperty('title')) {
            					if ($('#title-error').length == 0) {
            						$('#title').addClass('form-control-danger').after('<div id="title-error" class="form-control-feedback">' + data.results.title + '</div>');
            						$('#title').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#title-error').length > 0) {
            						$('#title-error').remove();
            						$('#title').removeClass('form-control-danger');
            						$('#title').parent().removeClass('has-danger');
            					}
        					}

           				if (data.results.hasOwnProperty('firstName')) {
            					if ($('#firstName-error').length == 0) {
            						$('#firstName').addClass('form-control-danger').after('<div id="firstName-error" class="form-control-feedback">' + data.results.firstName + '</div>');
            						$('#firstName').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#firstName-error').length > 0) {
            						$('#firstName-error').remove();
            						$('#firstName').removeClass('form-control-danger');
            						$('#firstName').parent().removeClass('has-danger');
            					}
        					}

           				if (data.results.hasOwnProperty('lastName')) {
            					if ($('#lastName-error').length == 0) {
            						$('#lastName').addClass('form-control-danger').after('<div id="lastName-error" class="form-control-feedback">' + data.results.lastName + '</div>');
            						$('#lastName').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#lastName-error').length > 0) {
            						$('#lastName-error').remove();
            						$('#lastName').removeClass('form-control-danger');
            						$('#lastName').parent().removeClass('has-danger');
            					}
        					}

           				if (data.results.hasOwnProperty('address1')) {
            					if ($('#address1-error').length == 0) {
            						$('#address1').addClass('form-control-danger').after('<div id="address1-error" class="form-control-feedback">' + data.results.address1 + '</div>');
            						$('#address1').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#address1-error').length > 0) {
            						$('#address1-error').remove();
            						$('#address1').removeClass('form-control-danger');
            						$('#address1').parent().removeClass('has-danger');
            					}
        					}

           				if (data.results.hasOwnProperty('address2')) {
            					if ($('#address2-error').length == 0) {
            						$('#address2').addClass('form-control-danger').after('<div id="address2-error" class="form-control-feedback">' + data.results.address2 + '</div>');
            						$('#address2').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#address2-error').length > 0) {
            						$('#address2-error').remove();
            						$('#address2').removeClass('form-control-danger');
            						$('#address2').parent().removeClass('has-danger');
            					}
        					}

           				if (data.results.hasOwnProperty('city')) {
            					if ($('#city-error').length == 0) {
            						$('#city').addClass('form-control-danger').after('<div id="city-error" class="form-control-feedback">' + data.results.city + '</div>');
            						$('#city').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#city-error').length > 0) {
            						$('#city-error').remove();
            						$('#city').removeClass('form-control-danger');
            						$('#city').parent().removeClass('has-danger');
            					}
        					}

         				if (data.results.hasOwnProperty('zip')) {
            					if ($('#zip-error').length == 0) {
            						$('#zip').addClass('form-control-danger').after('<div id="zip-error" class="form-control-feedback">' + data.results.zip + '</div>');
            						$('#zip').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#zip-error').length > 0) {
            						$('#zip-error').remove();
            						$('#zip').removeClass('form-control-danger');
            						$('#zip').parent().removeClass('has-danger');
            					}
        					}

         				if (data.results.hasOwnProperty('emailAddress')) {
            					if ($('#email-error').length == 0) {
            						$('#email').addClass('form-control-danger').after('<div id="email-error" class="form-control-feedback">' + data.results.emailAddress + '</div>');
            						$('#email').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#email-error').length > 0) {
            						$('#email-error').remove();
            						$('#email').removeClass('form-control-danger');
            						$('#email').parent().removeClass('has-danger');
            					}
        					}

         				if (data.results.hasOwnProperty('primaryPhone')) {
            					if ($('#phone-error').length == 0) {
            						$('#phone').addClass('form-control-danger').after('<div id="phone-error" class="form-control-feedback">' + data.results.primaryPhone + '</div>');
            						$('#phone').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#phone-error').length > 0) {
            						$('#phone-error').remove();
            						$('#phone').removeClass('form-control-danger');
            						$('#phone').parent().removeClass('has-danger');
            					}
        					}

         				if (data.results.hasOwnProperty('fax')) {
            					if ($('#fax-error').length == 0) {
            						$('#fax').addClass('form-control-danger').after('<div id="fax-error" class="form-control-feedback">' + data.results.fax + '</div>');
            						$('#fax').parent().addClass('has-danger');
            					}
        					} else {
            					if ($('#fax-error').length > 0) {
            						$('#fax-error').remove();
            						$('#fax').removeClass('form-control-danger');
            						$('#fax').parent().removeClass('has-danger');
            					}
        					}

        				} else if(data.status == 'success') {

        					removeError('entityName');
        					removeError('title');
        					removeError('firstName');
        					removeError('lastName');
        					removeError('address1');
        					removeError('address2');
        					removeError('city');
        					removeError('zip');
        					removeError('email');
        					removeError('phone');
        					removeError('fax');

                			if (data.hasOwnProperty('statusMessage')) {

                    			if ($('#successMessage').length == 0) {
                    				$('#formRegister').before('<div id="successMessage" class="alert alert-success" role="alert">' + data.statusMessage + '</div>');
                    			}

                    		}

        				}

        			}

        	    },
        	    error: function(err) {
        	    }
        	});

    });

 });

 </script>
