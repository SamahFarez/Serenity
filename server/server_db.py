from supabase import create_client, Client
import socket
import sys
from datetime import datetime

# Supabase configuration
url = "https://jntnkbzdcznzdvbaurwe.supabase.co/"  # Replace with your Supabase URL
key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImpudG5rYnpkY3puemR2YmF1cndlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MjgzMDY1MjgsImV4cCI6MjA0Mzg4MjUyOH0.3DU3i-oKXX0mhEVvZXtbyUZjctsCN20DDPrzQiRUwPs"   # Replace with your Supabase Key
supabase: Client = create_client(url, key)

# Define constants for the server
HOST = '127.0.0.1'  # Localhost
PORT = 65432        # Non-privileged port for listening


def verify_card(card_id):
    """Check if the card ID is valid."""
    return len(card_id) == 1


def get_card_details(card_id):
    """Fetch card details from the Cartes table by card ID."""
    response = supabase.table("cartes").select("*").eq("id_carte", card_id).execute()
    return response.data


def get_user_details(user_id):
    """Fetch user details from the Users table by user ID."""
    response = supabase.table("users").select("*").eq("id_user", user_id).execute()
    return response.data


def validate_card_usage(card_details):
    """Check if the card can be used."""
    if card_details:
        card = card_details[0]  # Assuming only one card_id is unique
        if not card['fonctionnel']:
            return "Access Denied: Card Not Functional"
        if card['usage_status']:
            return "Access Denied: Card Already Used"
        return "Card Valid"
    return "Access Denied: Card Not Found"


def validate_expiry_date(expiry_date, card_details):
    """Check if the card has expired."""
    try:
        expiry_date_input = datetime.strptime(expiry_date, '%Y-%m-%d')
        expiry_date_db = datetime.strptime(card_details[0]['expiry_date'], '%Y-%m-%d')

        if expiry_date_input > expiry_date_db:
            return "Access Denied: Card Expired"
    except ValueError:
        return "Error: Invalid Date Format"

    return "Expiry Date Valid"


def validate_session_time(session_start_time, card_details):
    """Check if the session start time is valid."""
    if session_start_time > card_details[0]['start_session_time']:
        return "Access Denied: Too Late"
    return "Session Time Valid"


def handle_client_connection(conn):
    """Handle the client connection and process card validation."""
    try:
        # Step 1: Receive Card_ID
        card_id = conn.recv(1024).decode().strip()
        if not verify_card(card_id):
            conn.sendall(b"Access Denied: Card ID Invalid\n")
            return

        conn.sendall(b"Card ID Valid\n")
        card_details = get_card_details(card_id)
        if not card_details:
            conn.sendall(b"Access Denied: Card Not Found\n")
            return

        # Step 2: Receive Expiry Date
        expiry_date = conn.recv(1024).decode().strip()
        expiry_date_validation = validate_expiry_date(expiry_date, card_details)
        if expiry_date_validation != "Expiry Date Valid":
            conn.sendall(expiry_date_validation.encode() + b"\n")
            return

        conn.sendall(b"Expiry Date Valid\n")

        # Step 3: Receive Usage Status
        usage_status = conn.recv(1024).decode().strip()
        if usage_status == "1":
            conn.sendall(b"Access Denied: Card Already Used\n")
            return

        conn.sendall(b"Card Not Used Before\n")

        # Step 4: Receive Session Start Time
        session_start_time = conn.recv(1024).decode().strip()
        session_time_validation = validate_session_time(session_start_time, card_details)
        if session_time_validation != "Session Time Valid":
            conn.sendall(session_time_validation.encode())
            return

        # Step 5: Finalize the transaction
        conn.sendall(b"Transaction Complete. FIN\n")
        
        # Retrieve card and user details
        card = card_details[0]  # Get card details
        user_id = card['id_user']  # Assuming the card has a user_id column
        user_details = get_user_details(user_id)  # Fetch user details

        # Print card and user details
        print("Card Details:", card)
        if user_details:
            print("User Details:", user_details[0])  # Assuming user details contain only one entry
        else:
            print("User Not Found for User ID:", user_id)

        print("Transaction finished with client.")
    except Exception as e:
        print(f"Error handling client connection: {e}")
    finally:
        conn.close()


def start_server():
    """Start the server and listen for connections."""
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as server_socket:
        server_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        server_socket.bind((HOST, PORT))
        server_socket.listen()
        print(f"Server listening on {HOST}:{PORT}")

        while True:
            conn, addr = server_socket.accept()
            print(f"Connected by {addr}")
            handle_client_connection(conn)


if __name__ == "__main__":
    start_server()
