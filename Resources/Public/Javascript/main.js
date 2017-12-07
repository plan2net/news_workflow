function ajaxCall(newsId, btn) {
	$.ajax({
		url: TYPO3.settings.ajaxUrls['WorkflowController::copyNews'],
		type: 'get',
		data: {
			newsId: newsId
		},
		success: function (jqXHRObject, status) {
			console.log(status);
			console.log(jqXHRObject);
			if (status !== 'success') {
				top.TYPO3.Notification.error("Newsworklow", jqXHRObject, Notification.ERROR, 5);
			}
			else {
				top.TYPO3.Notification.success("Newsworklow", jqXHRObject, Notification.OK, 5);
				btn.disabled = true;
				btn.style.background = 'white';
				btn.style.color = '#D3D3D3';
				btn.style.border = 'none';
			}


		},
		error: function (data) {
			top.TYPO3.Notification.error("Newsworklow", "Fehler beim Kopieren der News.", Notification.ERROR, 5);

		},
		cache: false
	});
}
