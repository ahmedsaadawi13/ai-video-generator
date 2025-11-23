// FILE: /app/views/subscription/invoices.php
<h1>Invoices</h1>
<table class="table">
    <tr><th>Invoice #</th><th>Amount</th><th>Status</th><th>Due Date</th><th>Paid</th></tr>
    <?php foreach ($invoices as $invoice): ?>
        <tr>
            <td><?php echo $this->escape($invoice['invoice_number']); ?></td>
            <td>$<?php echo number_format($invoice['total'], 2); ?></td>
            <td><span class="badge badge-<?php echo $invoice['status']; ?>"><?php echo $invoice['status']; ?></span></td>
            <td><?php echo formatDate($invoice['due_date']); ?></td>
            <td><?php echo $invoice['paid_at'] ? formatDate($invoice['paid_at']) : '-'; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
