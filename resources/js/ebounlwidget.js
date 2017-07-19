function EbouNlWidget(id) {
	this.id = id;
	this.spinner = jQuery('#' + this.id + '-ebou-nl-spinner'),
	this.success_msg = jQuery('#' + this.id + '-ebou-nl-success'),
	this.error_msg = jQuery('#' + this.id + '-ebou-nl-error');
}

EbouNlWidget.prototype.init = function() {
	var self = this;

	this.success_msg.on('click', function() {self.success_msg.hide();});
	this.error_msg.on('click', function() {self.error_msg.hide()});

	jQuery('#' + this.id + '-ebou-nl-subscribe').on('click', function() {
		
		self.success_msg.hide();
		self.error_msg.hide();

		var user_email = jQuery('#' + self.id + '-ebou-user-email').val(),
			file_id = jQuery('#' + self.id + '-ebou-nl-file option:selected').val(),
			mail_regex = /^[\w\-]+(\.[\w\-]+)*@([\w\-]+\.)*\w[\w\-]+\.[a-z]{2,6}(\.[a-z]{2,3})?$/i;

		if(file_id == undefined) {
			self.error_msg.text(invalid_nl_msg); // wordpress-localized message
			self.error_msg.show();
		} else if(user_email == undefined || user_email == "" || !mail_regex.test(user_email)) {
			self.error_msg.text(invalid_mail_msg); // wordpress-localized message
			self.error_msg.show();
		} else {
			self.spinner.show();
			jQuery.ajax({
				url: ebou_nl_api_url + file_id,
				type: 'POST',
				data: {
					followerEmail: user_email
				},
				beforeSend: function(xhrObj){
					xhrObj.setRequestHeader(ebou_nl_api_apikey_referer, ebou_nl_api_key);
				}
			}).always(function() {
				self.spinner.hide();
			}).success(function() {
				self.success_msg.text(success_subscribe_msg); // wordpress-localized message
				self.success_msg.show();
			}).fail(function() {
				self.error_msg.text(unknow_error_msg); // wordpress-localized message
				self.error_msg.show();
			});
		}
	});
}