# Event Ticketing Platform

A backend-focused event ticketing system built with Laravel, developed as part of a backend development internship.

## What it does

- Browse live events with real ticket pricing and availability
- View detailed information for each individual event
- Backend API structure ready for ticket purchasing and payment processing

## Tech stack

- Laravel 11
- MySQL / MariaDB
- Tailwind CSS
- Eloquent ORM

## Key backend features

- Relational database schema covering events, ticket types, orders, order items, and tickets
- Eloquent model relationships (one-to-many, belongsTo, many-to-many via a tags system)
- RESTful API routes, versioned under `/api/v1`
- Custom middleware for route protection
- Form request validation for ticket purchases
- Paystack payment service skeleton, including webhook handling with signature verification

## Status

Actively in development. Core event browsing is complete. Ticket purchasing and payment processing are the next planned phase.
