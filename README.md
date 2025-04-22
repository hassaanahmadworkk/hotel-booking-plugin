# Hotel Booking Plugin

A lightweight and customizable hotel booking plugin for WordPress. Easily manage hotel listings, rooms, and bookings with custom post types, dynamic user data, and a clean file structure.

## 🔧 Features

- 🧩 **Shortcode for Hotel Search**
  - Use `[g9x_search_rooms]` to display a hotel search form on any page.
  - Allows users to search hotels by **location**, **check-in**, and **check-out** dates.

- 🏨 **Custom Post Types**
  - `g9x_booking` – Manage and store user bookings.
  - `g9x_room` – Add and manage room listings with rich meta details.

- 📋 **Metaboxes**
  - Add custom fields for room details and booking information using WordPress metaboxes.

- 👥 **Dynamic User Data**
  - Automatically fetch and store the logged-in WordPress user’s name during booking confirmation.

- 🔢 **Booking ID Generation**
  - Each booking is assigned a unique Booking ID for easy tracking and management.

- 🗃 **Separate Database Table**
  - Booking data is saved in a custom WordPress database table for better performance and easier data handling.

- 🗂 **Modular File Structure**
  - Clean, well-organized files for easier customization and maintenance.

- 💵 **Manual Payment Handling**
  - Payment methods are **not yet integrated**.
  - Upon booking confirmation, you must **share your account/bank details with the customer via email or phone**.
  - Admin must **confirm the payment manually** and then **update the booking status** from the WordPress dashboard.

## 🚀 How to Use

1. **Install and Activate the Plugin**
   - Upload the plugin folder to `/wp-content/plugins/` and activate it via the Plugins menu.

2. **Use the Shortcode**
   - Add the following shortcode to any page or post:
     ```php
     [g9x_search_rooms]
     ```

3. **Add Room Listings**
   - Go to the WordPress dashboard → **Rooms** → **Add New**.
   - Fill in all room-related meta fields (price, capacity, etc.).

4. **Manage Bookings**
   - View and manage all bookings under the **Bookings** post type.
   - Update statuses once payment is confirmed.

5. **Manual Payment Instructions**
   - After a booking is submitted, contact the customer using the provided phone number or email.
   - Share your **bank/account details** for manual payment.
   - Once payment is received, update the booking status from the admin dashboard.

## 🧠 Developer Friendly

- Built with extendability in mind.
- Easily hook into booking actions, filters, and templates.
- Clean file structure for quick updates and feature additions.

## 📁 File Structure

