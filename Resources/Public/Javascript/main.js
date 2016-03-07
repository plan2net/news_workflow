function ajaxCall(newsId, btn) {
    new Ajax.Request(TYPO3.settings.ajaxUrls['WorkflowController::copyNews'], {
        method: 'get',
        parameters: '&newsId=' + newsId,
        onComplete: function(xhr, json) {
            if (xhr.status == 500) {
                TYPO3.Flashmessage.display(
                    TYPO3.Severity.error,
                    "Fehler beim Kopieren der News.",
                    xhr.responseText,
                    5
                );
            } else {
                TYPO3.Flashmessage.display(
                    TYPO3.Severity.ok,
                    "News wurde erfolgreich übermittelt.",
                    xhr.responseText,
                    5
                );
            }
        }
    });

    btn.disabled = true;
    btn.style.background = 'white';
    btn.style.color = '#D3D3D3';
    btn.style.border = 'none';
}
