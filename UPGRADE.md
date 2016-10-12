UPGRADE
=======

## Upgrade FROM  0.5 to 0.6

This version is compatible with Rollerworks Datagrid v0.11 and Twig v1.26.

The following functions are renamed: 

* `rollerworks_datagrid_widget` renamed to `rollerworks_datagrid`
* `rollerworks_datagrid_column_header_widget` renamed to `rollerworks_datagrid_column_header`
* `rollerworks_datagrid_cell_widget` renamed to `rollerworks_datagrid_column_cell`
* `rollerworks_datagrid_attributes_widget` renamed to `rollerworks_datagrid_attributes`

The following functions are removed, you need to pull there content
back into the main block (eg. `datagrid_container`):

* `rollerworks_datagrid_header_widget`
* `rollerworks_datagrid_rowset_widget`

### Theme support

Themes no longer allow to pass variables to a theme when loading.
You can still define variables before loading the theme, which will
accessible within in the theme (through context inheritance).

### Block name conventions

The conventions of block-names changed to be more in-inline with the Symfony Form
Component (which served as the main inspiration for the Datagrid's architecture).

A column with type `custom` and parent type `text` which intern is a child of
type `column` will now search for the following block-prefixes (in order):

1. The unique-prefix of element eg. `_my_datagrid_id` for a column named 'id'
   in datagrid '_my_datagrid'.  *This block can be changed the 'unique_prefix' option.*
2. `custom`
3. `text`
4. `column`

To define a header theme for type `custom` define block `custom_header`.
Or `_my_datagrid_id_header` for the `id` element only.

If the `custom_header` block doesn't exist, it falls back to `text_header`
and `column_header` as last.

**Note:** The sub-cells of a compound column include there name of
holder, eg. `_my_datagrid_actions_modify` for a compound column named `actions`.

The "datagrid" block is suffixed with `container`, eg. `datagrid_container` 
or `_my_datagrid_container`.
