<!DOCTYPE html>
<html lang="en">
<body>

<form action="<?= site_url('upload/save') ?>" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="attachment" id="attachment">
    <input type="submit" value="Upload Image" name="submit">
</form>

</body>
</html>
