async function refreshReports() {
    const start = document.getElementById('dateStart').value;
    const end = document.getElementById('dateEnd').value;

    // Fetch data from your API endpoint
    const response = await fetch(`api/get_reports.php?start=${start}&end=${end}`);
    const data = await response.json();

    // 1. Update Category Chart
    updateChart(categoryChart, data.categories.labels, data.categories.values);

    // 2. Update Table
    const tbody = document.getElementById('reportsTableBody');
    tbody.innerHTML = data.orders.map(order => `
        <tr>
            <td>#${order.order_id}</td>
            <td>${order.product_name} (${order.size_name})</td>
            <td>${order.barangay}</td>
            <td><span class="badge ${order.order_method === 'Delivery' ? 'bg-info' : 'bg-warning'}">${order.order_method}</span></td>
            <td class="fw-bold">₱${parseFloat(order.total_amount).toLocaleString()}</td>
            <td>${order.order_date}</td>
        </tr>
    `).join('');
}

// Chart Initializers (Standardized)
function updateChart(chart, labels, values) {
    chart.data.labels = labels;
    chart.data.datasets[0].data = values;
    chart.update();
}