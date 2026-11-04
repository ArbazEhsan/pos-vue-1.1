@extends('../master')

@section('title', 'All Invoice')
@section('prob', 'All Invoice')

@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title"> View {{$from}} Invoice</h4>
      </div>
      <div class="card-body">
        <div class="flex space-x-2 mb-4">
            <input type="date" id="start_date" class="border p-2 rounded" placeholder="Start Date">
            <input type="date" id="end_date" class="border p-2 rounded" placeholder="End Date">
            <button id="filter" class="btn btn-primary text-white px-4 py-2 rounded">Filter</button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover datatable">
            <thead>
              <tr>
                <th>Day</th>
                <th>Invoice_No</th>      
                <th>Reference</th>
                <th>Action</th>
              </tr>
            </thead> 
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script type="text/javascript">  

  $(document).ready(function () {
      function loadTable(startDate = "", endDate = "") {
          $(".datatable").DataTable().destroy(); // Destroy existing table before reloading

          $(".datatable").DataTable({
              processing: true,
              serverSide: false, // Change to true if dataset is huge
              ajax: {
                  url: "/getSInvoice",
                  type: "GET",
                  data: { start_date: startDate, end_date: endDate, from: "{!! $from !!}" }, // Send date filters
                  dataSrc: "data"
              },
              columns: [
                  { data: "day", render: function (data) { return new Date(data).toLocaleDateString(); } },
                  { data: "id" },
                  { data: "ref" },
                  {
                      data: "id",
                      render: function (data) {
                          return `<a class="btn btn-primary" target="_blank" href="/{!! $url !!}/${data}">Print</a>`;
                      },
                      orderable: false,
                  },
              ],
              dom: "<'flex items-center justify-between' <'flex items-center space-x-4' l> f> r t i p",
          });
      }

      // Load table initially without filters
      loadTable();

      // Apply filter on button click
      $("#filter").on("click", function () {
          let startDate = $("#start_date").val();
          let endDate = $("#end_date").val();
          loadTable(startDate, endDate);
      });
  });

</script>
@endsection