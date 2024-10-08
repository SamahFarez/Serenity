from supabase import create_client, Client
import socket
import sys

# Supabase configuration
url = "https://jntnkbzdcznzdvbaurwe.supabase.co/"  # Replace with your Supabase URL
key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImpudG5rYnpkY3puemR2YmF1cndlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MjgzMDY1MjgsImV4cCI6MjA0Mzg4MjUyOH0.3DU3i-oKXX0mhEVvZXtbyUZjctsCN20DDPrzQiRUwPs"   # Replace with your Supabase Key
supabase: Client = create_client(url, key)

# Define constants for the server
HOST = '127.0.0.1'  # Localhost
PORT = 65432        # Non-privileged port for listening

# Dummy validation functions
def verify_card(card_id):
    return len(card_id) == 1

def get_card_details(card_id):
    """Fetch card details from the Cartes table by card ID."""
    response = supabase.table("cartes").select("*").eq("id_carte", card_id).execute()
    return response.data

def get_user_details(user_id):
    """Fetch user details from the Users table by user ID."""
    response = supabase.table("users").select("*").eq("id_user", user_id).execute()  # Assuming 'id' is the correct column name
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
    if expiry_date > card_details[0]['expiry_date']:
        return "Access Denied: Card Expired"
    return "Expiry Date Valid"

def validate_session_time(session_start_time, card_details):
    """Check if the session start time is valid."""
    if session_start_time > card_details[0]['start_session_time']:
        return "Access Denied: Too Late"
    return "Session Time Valid"

# Set up the server
with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as server_socket:
    server_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)  # Allow port reuse
    server_socket.bind((HOST, PORT))  # Bind the socket to host and port
    server_socket.listen()  # Listen for incoming connections
    print(f"Server listening on {HOST}:{PORT}")

    conn, addr = server_socket.accept()  # Accept a connection
    with conn:
        print(f"Connected by {addr}")

        # Step 1: Receive Card_ID
        card_id = conn.recv(1024).decode().strip()
        if verify_card(card_id):
            conn.sendall(b"Card ID Valid\n")
        else:
            conn.sendall(b"Access Denied: Card ID Invalid\n")
            conn.close()  # Close the connection after informing the client
            sys.exit(0)

        # Check card details
        card_details = get_card_details(card_id)
        if not card_details:
            conn.sendall(b"Access Denied: Card Not Found\n")
            conn.close()
            sys.exit(0)

        # Step 2: Receive Expiry Date
        expiry_date = conn.recv(1024).decode().strip()
        expiry_date_validation = validate_expiry_date(expiry_date, card_details)
        if expiry_date_validation != "Expiry Date Valid":
            conn.sendall(expiry_date_validation.encode())
            conn.close()  # Close the connection after informing the client
            sys.exit(0)

        # Step 3: Receive Usage Status
        usage_status = conn.recv(1024).decode().strip()
        if usage_status == "1":
            conn.sendall(b"Access Denied: Card Already Used\n")
            conn.close()  # Close the connection after informing the client
            sys.exit(0)
        else:
            conn.sendall(b"Card Not Used Before\n")

        # Step 4: Receive Session Start Time
        session_start_time = conn.recv(1024).decode().strip()
        session_time_validation = validate_session_time(session_start_time, card_details)
        if session_time_validation != "Session Time Valid":
            conn.sendall(session_time_validation.encode())
            conn.close()  # Close the connection after informing the client
            sys.exit(0)

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
