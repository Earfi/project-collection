# Agent Notes

## Project
- Name: Collection App
- Stack: Laravel + Blade + SQLite
- Main goal: Personal collectibles catalog with public view and owner-only management

## Current Features
- Public collection list with filters and infinite loading
- Admin login/logout
- Add/Edit/Delete collectible items
- Image upload with watermark
- Light/Dark mode
- TH/EN locale switch
- Item card modal details
- Image focus position (X/Y) for card display

## Data Model (`items`)
- `type`
- `scale`
- `maker`
- `subject_brand`
- `name`
- `description`
- `price`
- `qty`
- `collected_at`
- `image_path`
- `image_focus_x`
- `image_focus_y`

## UI Conventions
- Keep design minimal and readable
- Card image uses square ratio
- Card text should clamp/ellipsis to keep equal card heights
- Type is shown as compact pill

## Form Behavior
- `qty` default is `1`
- `scale`, `maker`, `subject_brand` are combo inputs:
  - Suggest values from selected `type` history first
  - Allow entering new custom text

## TODO / Ideas
- Add small hint when value is new for selected type
- Add export/backup sync flow to Google Sheets
- Add tests for item form metadata behavior

