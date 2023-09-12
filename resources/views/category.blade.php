<form action="{{ route('createCategory') }}" method="post"  enctype="multipart/form-data">
  @csrf
  <input type="text" name="name" placeholder="Category name">
  <input type="file" name="image" placeholder="Category image">
  <input type="submit" value="Create category">
</form>