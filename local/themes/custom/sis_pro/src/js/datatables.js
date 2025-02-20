(function ($, Drupal, once) {
  Drupal.behaviors.dataTablesInit = {
    attach: function (context, settings) {
      console.log("🔹 DataTables behavior triggered!");

      function initializeStudentLevelTable(context) {
        console.log("✅ Student Level Manager Table Detected! Applying custom DataTables settings.");

        // ✅ Apply scrolling settings
        once('dataTablesInit', '#student-level-table', context).forEach((table) => {
          console.log("✅ Initializing DataTable for Student Level Manager...");
          $(table).DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: true,
            pageLength: 20, // ✅ Default to 20 rows per page
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
            autoWidth: true, // ✅ Allow auto-sizing
            fixedHeader: true, // ✅ Enable fixed header
            scrollY: "500px", // ✅ Enables vertical scrolling
            scrollCollapse: true,
            columnDefs: [
              { targets: [1, 2, 3], orderable: true }, // ✅ Sorting enabled for Korean Name, English Name, and Level
              { targets: "_all", orderable: false } // ✅ Sorting disabled for all other columns
            ],
            dom: '<"top"f>rt<"bottom"lip>'
          });
        });
      }

      function initializeSiteWideTables(context) {
        once('dataTablesInit', '#myTable', context).forEach((table) => {
          console.log("✅ Found site-wide DataTable: ", table);
          $(table).DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: true,
            pageLength: 15, // ✅ Default number of rows per page
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            autoWidth: false,
            fixedHeader: false,
            columnDefs: [
              { width: "150px", targets: "_all" } // ✅ Set default column width
            ],
            dom: '<"top"Bf>rt<"bottom"lip>',
            buttons: [
              'copyHtml5',
              'csvHtml5',
              'excelHtml5',
              {
                extend: 'pdfHtml5',
                text: 'PDF',
                customize: function (doc) {
                  console.log("✅ PDF customization applied!");
                  doc.defaultStyle = { fontSize: 12, alignment: 'center' };
                  if (doc.content && doc.content[1] && doc.content[1].table) {
                    doc.content[1].table.widths = Array(doc.content[1].table.body[0].length).fill('auto');
                    doc.content[1].table.body.forEach(row => {
                      row.forEach(cell => {
                        if (cell) {
                          cell.alignment = 'center';
                          cell.margin = [5, 5, 5, 5];
                        }
                      });
                    });
                  } else {
                    console.warn("⚠️ No table found in PDF content, skipping column width modifications.");
                  }
                }
              },
              'print'
            ]
          });
        });
      }

      // ✅ Ensure DataTables reinitializes for Student Level Manager after AJAX updates
      $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.url.includes('student-level-manager-form')) {
          console.log("🔄 AJAX Update Detected! Reinitializing DataTables for Student Manager...");
          initializeStudentLevelTable(document);
        }
      });

      // ✅ Initialize the correct tables
      if ($('#student-level-table', context).length) {
        initializeStudentLevelTable(context);
      } else {
        initializeSiteWideTables(context);
      }
    }
  };
})(jQuery, Drupal, once);
