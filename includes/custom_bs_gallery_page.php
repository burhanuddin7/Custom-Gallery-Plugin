<div class="media-plugin-wrapper">
    <div class="loader"></div>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Add Image</h4>
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <form action="" method="post" enctype="multipart/form-data">
                                  <div class="form-group row">
                                    <label for="packagename" class="col-4 col-form-label">Class Name</label> 
                                    <div class="col-8">
                                      <input id="packagename" name="packagename" placeholder="Class Name" class="form-control here" type="text">
                                    </div>
                                  </div>
                                  <div class="form-group row">
                                    <label for="packagephoto" class="col-4 col-form-label">Gallery Photo</label> 
                                    <div class="col-8">
                                      <input id="packagephoto" name="packagephoto" class="form-control here" type="file">
                                    </div>
                                  </div>
                                  <div class="form-group row">
                                    <div class="offset-4 col-8">
                                      <input id="addimage" name="addimage" class="btn btn-primary" value="Add Image" type="button">
                                    </div>
                                  </div>
                                </form>
                        </div>
                    </div>  
                </div>
            </div>
        </div>
      </div>
    </div>
  <div class="button-holder">
      <input id="deleteImage" onclick="deleteGalleryImagesFromDB()" name="deleteImage" class="btn btn-primary" value="Delete" type="button">
  </div>
  <div class="image-gallery-wrapper" id="preview">
  </div>
</div>