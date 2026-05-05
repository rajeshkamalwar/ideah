{{-- Synced on each page load from HomeController (all active categories for current language). --}}
<select aria-label="categories" id="category_id" name="category_id" class="select2 js-example-basic-single1">
  <option value=""></option>
  @foreach ($hero_categories ?? [] as $heroCat)
    <option value="{{ $heroCat->slug }}">{{ $heroCat->name }}</option>
  @endforeach
</select>
