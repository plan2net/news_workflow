1) Install Extension (using composer is also possible)
2) Create Folder where to put the copied news records
3) Configure Extension
   1) Set the following PageTS in the folder where the original news records are located

        user.tx_news_workflow {
        # PID of target folder for copy operation (folder that form step 2)
            approvalTargetPid = 12708
        # ID of category records to add for approval
            approvalCategories = 30
        }
    2) optional: Set value of the constant plugin.tx_newsworkflow.emailSender

4) Configure task runner:
    1) Exbase Command Controller-Task
    2) NewsWorkflow Email:sendMail
    3) Set Arguments:
        a) ID of the folder where the copied news are located
        b) List of recipients, comma separated
