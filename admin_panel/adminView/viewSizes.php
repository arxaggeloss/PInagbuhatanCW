<div>
    <h2>Events</h2>
    <table class="table">
        <thead>
            <tr>
                <th class="text-center">ID</th>
                <th class="text-center">Title</th>
                <th class="text-center">Description</th>
                <th class="text-center">Start Date and Time</th>
                <th class="text-center">End Date and Time</th>
            </tr>
        </thead>
        <?php
        include_once "../config/dbconnect.php";
        $sql = "SELECT id, title, description, start_datetime, end_datetime FROM events";
        $result = $conn->query($sql);
        $count = 1;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        ?>
                <tr>
                    <td><?= $row["id"] ?></td>
                    <td><?= $row["title"] ?></td>
                    <td><?= $row["description"] ?></td>
                    <td><?= $row["start_datetime"] ?></td>
                    <td><?= $row["end_datetime"] ?></td>
                </tr>
        <?php
                $count++;
            }
        }
        ?>
    </table>
</div>
