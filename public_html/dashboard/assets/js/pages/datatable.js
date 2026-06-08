(function($) {
  'use strict'

  const ASSET_DIR = "assets/";

          const $tableEl = $(".datatables-basic");

          if (!$tableEl.length) return;

          // Update Select All checkbox based on row selections
          $(document).on("change", "input.dt-row-check", function () {
            const $rows = $tableEl.find("tbody input.dt-row-check");
            const total = $rows.length;
            const checked = $rows.filter(":checked").length;

            const $selectAll = $(".select-all-checkbox")[0];

            if (checked === 0) {
              $selectAll.checked = false;
              $selectAll.indeterminate = false;
            } else if (checked === total) {
              $selectAll.checked = true;
              $selectAll.indeterminate = false;
            } else {
              $selectAll.checked = false;
              $selectAll.indeterminate = true;
            }
          });


          // ----------------------------------------
          // Checkbox Select All Logic
          // ----------------------------------------
          $(document).on("change", ".select-all-checkbox", function () {
            const checked = $(this).is(":checked");
            $tableEl.find("tbody input.dt-row-check").prop("checked", checked).trigger("change");
          });


          // ----------------------------------------
          // Main DataTable init
          // ----------------------------------------
          const employeeTable = $tableEl.DataTable({

            ajax: `${ASSET_DIR}json/table-datatable.json`,

            columns: [
              { data: null },
              { data: null },
              { data: "id" },
              { data: "name" },
              { data: "email" },
              { data: "designation" },
              { data: "salary" },
              { data: "status" },
              { data: null }
            ],

            order: [[2, "desc"]],

            responsive: {
              details: {
                type: "column",
                display: $.fn.dataTable.Responsive.display.modal({
                  header: function (row) {
                    let rowData = row.data();
                    return `Details of ${rowData.name}`;
                  }
                }),
                renderer: function (api, rowIdx, columns) {
                  let items = columns
                    .map(col => {
                      return col.title
                        ? `<tr><td>${col.title}</td><td>${col.data}</td></tr>`
                        : "";
                    })
                    .join("");

                  return items ? $('<table class="table table-sm"/>').append(items) : false;
                }
              }
            },

            dom:
              '<"card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between"lfB>' +
              't' +
              '<"row mt-2"<"col-md-6"i><"col-md-6"p>>',

            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],

            buttons: [
              {
                extend: "collection",
                className: "btn btn-label-primary dropdown-toggle",
                text: `<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.75 11.5192V12.5962C0.75 13.1674 0.976922 13.7152 1.38085 14.1192C1.78477 14.5231 2.33261 14.75 2.90385 14.75H12.5962C13.1674 14.75 13.7152 14.5231 14.1192 14.1192C14.5231 13.7152 14.75 13.1674 14.75 12.5962V11.5192M4.51923 4.51923L7.75 0.75M7.75 0.75L10.9808 4.51923M7.75 0.75V10.4423" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg> برون ریزی`,
                buttons: [
                  { extend: "print", text: "Print", className: "dropdown-item", exportOptions: { columns: [3,4,5,6,7] } },
                  { extend: "csv", text: "CSV", className: "dropdown-item", exportOptions: { columns: [3,4,5,6,7] } },
                  { extend: "excel", text: "Excel", className: "dropdown-item", exportOptions: { columns: [3,4,5,6,7] } },
                  { extend: "pdf", text: "PDF", className: "dropdown-item", exportOptions: { columns: [3,4,5,6,7] } },
                  { extend: "copy", text: "Copy", className: "dropdown-item", exportOptions: { columns: [3,4,5,6,7] } }
                ]
              }
            ],

            columnDefs: [
              {
                targets: 0,
                className: "control",
                orderable: false,
                searchable: false,
                render: () => ""
              },

              {
                targets: 1,
                orderable: false,
                searchable: false,
                render: () => '<input type="checkbox" class="dt-row-check form-check-input">'
              },

              // ▼ Hide internal ID column
              {
                targets: 2,
                visible: false
              },

              // ▼ Avatar + Name + Post
              {
                targets: 3,
                render: function (data, type, row) {
                  const img = row.avatar;
                  const name = row.name;
                  const job = row.designation;

                  const avatar =
                    img
                      ? `<img src="${ASSET_DIR}img/avatar/${img}" class="rounded-circle" alt="Avatar">`
                      : `<span class="avatar-text rounded-circle bg-label-primary">${name.charAt(0)}</span>`;

                  return `
                    <div class="d-flex align-items-center">
                      <div class="avatar me-2">${avatar}</div>
                      <div class="d-flex flex-column">
                        <span>${name}</span>
                        <small class="text-muted">${job}</small>
                      </div>
                    </div>
                  `;
                }
              },
              {
                targets: 7,
                render: function (data, type, row) {
                  const statuses = {
                    1: ["در حال همکاری", "bg-label-primary"],
                    2: ["حرفه‌ای", "bg-label-success"],
                    3: ["رد شده", "bg-label-danger"],
                    4: ["استعفا داده", "bg-label-warning"],
                    5: ["درخواست داده", "bg-label-info"]
                  };

                  if (!statuses[row.status]) return data;

                  return `<span class="badge ${statuses[row.status][1]}">${statuses[row.status][0]}</span>`;
                }
              },
              {
                targets: -1,
                orderable: false,
                searchable: false,
                render: function () {
                  return `
                    <div class="dropdown">
                        <button class="btn btn-sm btn-icon btn-icon-secondary dropdown-toggle hide-arrow rounded-pill" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="3" height="14" viewBox="0 0 3 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 12.5165C3 12.9231 2.85278 13.272 2.55833 13.5632C2.26389 13.8544 1.91111 14 1.5 14C1.08889 14 0.736112 13.8544 0.441667 13.5632C0.147223 13.272 0 12.9231 0 12.5165C0 12.1099 0.147223 11.761 0.441667 11.4698C0.736112 11.1786 1.08889 11.033 1.5 11.033C1.77222 11.033 2.02222 11.1016 2.25 11.239C2.47778 11.3709 2.66111 11.5495 2.8 11.7747C2.93333 11.9945 3 12.2418 3 12.5165Z" fill="currentColor" />
                                <path d="M3 7C3 7.40659 2.85278 7.75549 2.55833 8.0467C2.26389 8.33791 1.91111 8.48352 1.5 8.48352C1.08889 8.48352 0.736112 8.33791 0.441667 8.0467C0.147223 7.75549 0 7.40659 0 7C0 6.59341 0.147223 6.24451 0.441667 5.9533C0.736112 5.66209 1.08889 5.51648 1.5 5.51648C1.77222 5.51648 2.02222 5.58517 2.25 5.72253C2.47778 5.8544 2.66111 6.03297 2.8 6.25824C2.93333 6.47802 3 6.72527 3 7Z" fill="currentColor" />
                                <path d="M3 1.48351C3 1.89011 2.85278 2.23901 2.55833 2.53022C2.26389 2.82143 1.91111 2.96703 1.5 2.96703C1.08889 2.96703 0.736112 2.82143 0.441667 2.53022C0.147223 2.23901 0 1.89011 0 1.48351C0 1.07692 0.147223 0.728022 0.441667 0.436812C0.736112 0.145604 1.08889 0 1.5 0C1.77222 0 2.02222 0.0686817 2.25 0.206044C2.47778 0.337913 2.66111 0.516483 2.8 0.741757C2.93333 0.961538 3 1.20879 3 1.48351Z" fill="currentColor" />
                            </svg>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#">مشاهده</a></li>
                            <li><a class="dropdown-item" href="#">تکثیر</a></li>
                        </ul>
                    </div>
                  `;
                }
              }
            ]
          });

}(jQuery))