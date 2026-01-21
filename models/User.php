<?php
require_once __DIR__ . '/../controllers/db.php';
require_once __DIR__ . '/Auth.php';

function getUserData($user_id)
{
  return fetchOne("
    SELECT
      u.id,
      u.email,
      u.role,
      p.first_name,
      p.last_name,
      p.phone,
      p.gender,
      p.address,
      p.picture
    FROM users u
    JOIN user_profiles p ON u.id = p.user_id
    WHERE u.id = {$user_id}
  ");
}

function updateUserProfile($user_id, $first_name, $last_name, $phone)
{
  $first_name = sanitizeInput($first_name);
  $last_name = sanitizeInput($last_name);
  $phone = sanitizeInput($phone);

  return query("
    UPDATE user_profiles
    SET first_name = '$first_name',
        last_name = '$last_name',
        phone = '$phone'
    WHERE user_id = '$user_id'
  ");
}

function updateUserPicture($user_id, $filename)
{
  return query("
    UPDATE user_profiles
    SET picture = '$filename'
    WHERE user_id = '$user_id'
  ");
}

function updateUserPassword($user_id, $hashed_password)
{
  return query("
    UPDATE users
    SET password = '$hashed_password'
    WHERE id = '$user_id'
  ");
}

function getUserPassword($user_id)
{
  return fetchOne("SELECT password FROM users WHERE id = '$user_id'");
}

function updateUserAddress($user_id, $address)
{
  $address = sanitizeInput($address);

  return query("
    UPDATE user_profiles
    SET address = '$address'
    WHERE user_id = '$user_id'
  ");
}
