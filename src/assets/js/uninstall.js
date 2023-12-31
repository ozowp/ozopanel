document.addEventListener('DOMContentLoaded', function () {
	var modal = document.querySelector('.ozopanel-feedback-modal');
	var deactivateLink = '';

	// Open modal
	document.getElementById('the-list').addEventListener('click', function (e) {
		if (e.target.classList.contains('ozopanel-deactivate-link')) {
			e.preventDefault();
			modal.classList.add('ozopanel-show');
			deactivateLink = e.target.getAttribute('href');
		}
	});

	// Close modal; Cancel
	modal.addEventListener('click', function (e) {
		if (e.target.classList.contains('ozopanel-close')) {
			e.preventDefault();
			modal.classList.remove('ozopanel-show');
		}
	});

	// Reason change
	modal.addEventListener('click', function (e) {
		if (e.target.type === 'radio') {
			var parent = e.target.closest('.ozopanel-field-radio');
			document
				.querySelectorAll(
					'.ozopanel-feedback-text, .ozopanel-feedback-alert'
				)
				.forEach(function (element) {
					element.style.display = 'none';
				});
			document.querySelector('.ozopanel-data-alert').style.display =
				'block';
			parent.nextElementSibling.style.display = 'block';
		}
	});

	// Submit response
	modal.addEventListener('click', function (e) {
		if (e.target.tagName === 'BUTTON') {
			e.preventDefault();

			var btn = e.target;

			var submit = btn.classList.contains('ozopanel-feedback-submit');

			if (btn.classList.contains('disabled')) {
				return;
			}

			var radio = document.querySelector(
				'input[type="radio"]:checked',
				modal
			);
			var reasonKey = radio === null ? 'none' : radio.value;

			var input = document.querySelector(
				'.ozopanel-feedback-text input[name="reason_' + reasonKey + '"]'
			);
			var dataCollect = document.getElementById('ozopanel-data-collect')
				.checked
				? '1'
				: '';

			var nonce = document.querySelector(
				'.ozopanel-form-style-one input[name="_wpnonce"]'
			).value;

			if (reasonKey === 'none') {
				window.location.href = deactivateLink;
			} else {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', ozopanel.ajaxurl, true);
				xhr.setRequestHeader(
					'Content-Type',
					'application/x-www-form-urlencoded; charset=UTF-8'
				);
				xhr.onreadystatechange = function () {
					if (xhr.readyState === 4) {
						if (xhr.status === 200) {
							window.location.href = deactivateLink;
						} else {
							window.location.href = deactivateLink;
						}
					}
				};
				xhr.send(
					'action=ozopanel_deactivate_feedback&submit=' +
						submit +
						'&data_collect=' +
						dataCollect +
						'&nonce=' +
						nonce +
						'&reason_key=' +
						reasonKey +
						'&reason=' +
						(input ? input.value.trim() : '')
				);
				btn.classList.add('disabled');
				btn.textContent = 'Processing...';
			}
		}
	});
});
