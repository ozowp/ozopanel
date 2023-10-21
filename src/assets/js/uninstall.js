document.addEventListener('DOMContentLoaded', function () {
    var modal = document.querySelector('.wam-feedback-modal');
    var deactivateLink = '';

    // Open modal
    document.getElementById('the-list').addEventListener('click', function (e) {
        if (e.target.classList.contains('wam-deactivate-link')) {
            e.preventDefault();
            modal.classList.add('wam-show');
            deactivateLink = e.target.getAttribute('href');
        }
    });

    // Close modal; Cancel
    modal.addEventListener('click', function (e) {
        if (e.target.classList.contains('wam-close')) {
            e.preventDefault();
            modal.classList.remove('wam-show');
        }
    });

    // Reason change
    modal.addEventListener('click', function (e) {
        if (e.target.type === 'radio') {
            var parent = e.target.closest('.wam-field-radio');
            document.querySelectorAll('.wam-feedback-text, .wam-feedback-alert').forEach(function (element) {
                element.style.display = 'none';
            });
            document.querySelector('.wam-data-alert').style.display = 'block';
            parent.nextElementSibling.style.display = 'block';
        }
    });

    // Submit response
    modal.addEventListener('click', function (e) {
        if (e.target.tagName === 'BUTTON') {
            e.preventDefault();

            var btn = e.target;

            var submit = btn.classList.contains('wam-feedback-submit');

            if (btn.classList.contains('disabled')) {
                return;
            }

            var radio = document.querySelector('input[type="radio"]:checked', modal);
            var reasonKey = (radio === null) ? 'none' : radio.value;

            var input = document.querySelector('.wam-feedback-text input[name="reason_' + reasonKey + '"]');
            var dataCollect = document.getElementById('wam-data-collect').checked ? '1' : '';

            var nonce = document.querySelector('.wam-form-style-one input[name="_wpnonce"]').value;

            if (reasonKey === 'none') {
                window.location.href = deactivateLink;
            } else {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', wam.ajaxurl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            window.location.href = deactivateLink;
                        } else {
                            window.location.href = deactivateLink;
                        }
                    }
                };
                xhr.send('action=wam_deactivate_feedback&submit=' + submit + '&data_collect=' + dataCollect + '&nonce=' + nonce + '&reason_key=' + reasonKey + '&reason=' + (input ? input.value.trim() : ''));
                btn.classList.add('disabled');
                btn.textContent = 'Processing...';
            }
        }
    });
});
