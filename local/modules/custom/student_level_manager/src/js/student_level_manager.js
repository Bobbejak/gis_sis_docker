
(function ($, Drupal) {
    Drupal.behaviors.toggleAllStudents = {
        attach: function (context, settings) {
            once('toggleAll', '#select-all-students', context).forEach((element) => {
                $(element).change(function () {
                    var checked = $(this).is(':checked');
                    $('.student-checkbox', context).prop('checked', checked);
                });
            });
        }
    };
})(jQuery, Drupal);
