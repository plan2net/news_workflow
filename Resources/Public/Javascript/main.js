function ajaxCall (newsId, btn) {

    var success = new Ajax.Request(TYPO3.settings.ajaxUrls['WorkflowController::copyNews'], {
        method: 'get',
        parameters: '&newsId='+newsId,
        onComplete: function (xhr, json) {
            console.log(xhr);

            TYPO3.Flashmessage.display(
                TYPO3.Severity.ok,
                "Datensatz wurde übermittelt",
                xhr.responseText,
                5
            );
        }
    });

   btn.disabled = true;
   btn.style.background = 'white';
   btn.style.color = '#D3D3D3';
   btn.style.border = 'none';

}
