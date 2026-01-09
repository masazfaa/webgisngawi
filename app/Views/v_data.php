<div class="d-flex justify-content-between align-items-center mb-3">
  <button type="button" class="button" data-bs-toggle="modal" data-bs-target="#addDataModal">+ Add New Data</button>
  <input type="text" id="searchInput" class="form-control w-25" placeholder="Search...">
</div>

<div class="table-wrapper">
  <table id="dataTable" class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
        <tr>
          <td>ini id</td>
          <td>
            <div class="btn-group">
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editDataModal" 
                onclick="populateEditModal('')">
                Edit
              </button>
              <button class="btn btn-sm btn-danger" onclick="showDeleteModal()">Delete</button>
            </div>
          </td>
        </tr>
    </tbody>
  </table>
</div>

<nav id="pagination" class="mt-3">
  <ul class="pagination justify-content-center"></ul>
</nav>

<script>

document.addEventListener('DOMContentLoaded', function () {
  const rowsPerPage = 10;
  const rows = document.querySelectorAll('#dataTable tbody tr');
  const totalPages = Math.ceil(rows.length / rowsPerPage);

  // Function to update the table rows based on the current page
  function updateTablePage(page) {
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    
    // Hide all rows
    rows.forEach((row, index) => {
      row.style.display = 'none'; // Hide all rows
    });

    // Show the selected range of rows
    for (let i = start; i < end; i++) {
      if (rows[i]) {
        rows[i].style.display = ''; // Show row
      }
    }

    // Update pagination buttons
    updatePagination(page);

    // Store current page in localStorage
    localStorage.setItem('currentPage', page);
  }

  // Function to update pagination buttons
  function updatePagination(currentPage) {
    const pagination = document.querySelector('#pagination ul');
    pagination.innerHTML = ''; // Clear current pagination buttons

    // Create previous button
    const prevButton = document.createElement('li');
    prevButton.classList.add('page-item');
    prevButton.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
    prevButton.addEventListener('click', () => {
      if (currentPage > 1) {
        updateTablePage(currentPage - 1);
      }
    });
    pagination.appendChild(prevButton);

    // Create page buttons
    for (let i = 1; i <= totalPages; i++) {
      const pageButton = document.createElement('li');
      pageButton.classList.add('page-item');
      if (i === currentPage) {
        pageButton.classList.add('active');
      }
      pageButton.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      pageButton.addEventListener('click', () => {
        updateTablePage(i);
      });
      pagination.appendChild(pageButton);
    }

    // Create next button
    const nextButton = document.createElement('li');
    nextButton.classList.add('page-item');
    nextButton.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
    nextButton.addEventListener('click', () => {
      if (currentPage < totalPages) {
        updateTablePage(currentPage + 1);
      }
    });
    pagination.appendChild(nextButton);
  }

  // Check if there's a saved page in localStorage
  const savedPage = parseInt(localStorage.getItem('currentPage')) || 1;

  // Initialize pagination to saved page
  updateTablePage(savedPage);
});

</script>
