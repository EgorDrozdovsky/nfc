
$(document).on("click", ".open-model", function () {
    "use strict";
    $('#deleteModal').modal('show');
    var cardId = $(this).data('id');
    console.log(cardId);
    var link = "/user/card-status/"+ cardId;
    var preview = document.getElementById("plan_id"); //getElementById instead of querySelectorAll
    preview.setAttribute("href", link);
    // As pointed out in comments,
    // it is unnecessary to have to manually call the modal.
});

$(document).on("click", ".open-plan-model", function () {
    "use strict";
    $('#planModal').modal('show');
    var planId = $(this).data('id');
    var link = "/user/checkout/"+ planId;
    var preview = document.getElementById("plan_id"); //getElementById instead of querySelectorAll
    preview.setAttribute("href", link);
});

$(document).on("click", ".down-plan-model", function () {
    "use strict";
    $('#downPlanModel').modal('show');
});
