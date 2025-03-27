<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
</head>
<body>
    <h1>Form</h1>
    <form action="{{ route('post.save') }}" method="POST">
        @csrf

        <div>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div>
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required>
        </div>

        <button type="submit">Create Post</button>
    </form>
</body>
</html>
