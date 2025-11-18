<div class="col-sm-4 col-4">

    <div class="form-check frame-box">

        <input type="radio" class="btn-check" name="options-outlined" value="0"id="none-outlined" autocomplete="off"

            {{ $master->frame == '' ? 'checked' : '' }}>

        <label class="add-active" for="none-outlined">

            <img src="{{ asset('assets/images/frame-without.png') }}">

            <div class="con_framebox">

                <h5>No Frame</h5>

            </div>

        </label>

    </div>

</div>

@forelse($framService as $frameData)

    <div class="col-sm-4 col-4">

        <div class="form-check frame-box">

            <input type="radio" class="btn-check" name="options-outlined" id="{{ $frameData['frame_id'] }}"

                value="{{ $frameData['id'] }}" autocomplete="off"

                {{ $master->frame == $frameData['frame_id'] ? 'checked' : '' }}>

            <label class="add-active" for="{{ $frameData['frame_id'] }}">

                <img src="{{ asset($frameData['frame_image']) }}">

                <div class="con_framebox">

                    <h5>{{ $frameData['name'] }}</h5>

                </div>

            </label>

        </div>

    </div>

@empty

@endforelse

