function ajaxCall () {
    var success = new Ajax.Request(TYPO3.settings.ajaxUrls['WorkflowController::copyNews'], {
        parameters: '&shortcutId=copyNews',
        onComplete: function (xhr, json) {
            console.log(xhr);
            alert (xhr.responseText);
        }
    });
}
