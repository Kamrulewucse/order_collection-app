@extends('layouts.app')
@section('title', 'Campaign Details')
@section('style')
    <style>
       .campaign-image {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .campaign-image:hover {
            transform: scale(1.05);
        }
        #imageModal .modal-body {
        height: 400px; /* Fixed height for the modal body */
        display: flex;
        align-items: center;
        justify-content: center;
        }
        #modalImage {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain; /* Ensures the image scales proportionally */
        }
    </style>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">{{ $campaign->name }} - Images</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($campaign->images as $index => $image)
                        <div class="col-md-3 mb-3">
                            <img src="{{ asset($image->file_path) }}"
                                 class="img-thumbnail campaign-image"
                                 data-toggle="modal"
                                 data-target="#imageModal"
                                 data-image="{{ asset($image->file_path) }}"
                                 data-index="{{ $index }}"
                                 alt="Campaign Image" style="height:300px !important;">

                            <p class="mt-2 font-weight-bold text-center">{{ $image->file_name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal with Download Option and Navigation Arrows -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Campaign Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center position-relative" style="height: 400px; display: flex; align-items: center; justify-content: center;">
                <img src="" id="modalImage" class="img-fluid" alt="Modal Image" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                <!-- Left Arrow -->
                <button id="prevImage" class="btn btn-primary position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%);">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <!-- Right Arrow -->
                <button id="nextImage" class="btn btn-primary position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <div class="modal-footer">
                <a href="#" id="downloadImage" class="btn btn-success" download>
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        let currentIndex = 0;
        const images = @json($campaign->images->pluck('file_path')); // Get all image paths as an array

        // Function to update the modal image
        function updateModalImage(index) {
            const imageSrc = "{{ asset('') }}" + images[index];
            $('#modalImage').attr('src', imageSrc);
            $('#downloadImage').attr('href', imageSrc);
        }

        // Open modal and set the initial image
        $('.campaign-image').on('click', function() {
            currentIndex = $(this).data('index');
            updateModalImage(currentIndex);
        });

        // Navigate to the previous image
        $('#prevImage').on('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                updateModalImage(currentIndex);
            }
        });

        // Navigate to the next image
        $('#nextImage').on('click', function() {
            if (currentIndex < images.length - 1) {
                currentIndex++;
                updateModalImage(currentIndex);
            }
        });
    });
</script>
@endsection
