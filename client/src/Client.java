import java.io.*;
import java.net.*;

public class Client {
    private static final String HOST = "127.0.0.1";
    private static final int PORT = 65432;

    private Socket socket;
    private PrintWriter out;
    private BufferedReader in;
    private BufferedReader userInput;

    public Client() throws IOException {
        socket = new Socket(HOST, PORT);
        out = new PrintWriter(socket.getOutputStream(), true);
        in = new BufferedReader(new InputStreamReader(socket.getInputStream()));
        userInput = new BufferedReader(new InputStreamReader(System.in));
    }

    public void start() {
        try {
            System.out.println("Connected to server.");

            if (sendCardId() && sendExpiryDate() && sendUsageStatus() && sendSessionStartTime()) {
                receiveFinalResponse();
            }

        } catch (IOException e) {
            System.err.println("Error during communication: " + e.getMessage());
        } finally {
            close();
        }
    }

    private boolean sendCardId() throws IOException {
        System.out.print("Enter Card ID: ");
        String cardId = userInput.readLine();
        out.println(cardId);
        String response = in.readLine();
        System.out.println("Server response: " + response);
        return response.equals("Card ID Valid");
    }

    private boolean sendExpiryDate() throws IOException {
        System.out.print("Enter Expiry Date: ");
        String expiryDate = userInput.readLine();
        out.println(expiryDate);
        String response = in.readLine();
        System.out.println("Server response: " + response);
        return response.equals("Expiry Date Valid");
    }

    private boolean sendUsageStatus() throws IOException {
        System.out.print("Enter Usage Status (0 for not used, 1 for used): ");
        String usageStatus = userInput.readLine();
        out.println(usageStatus);
        String response = in.readLine();
        System.out.println("Server response: " + response);
        return !response.equals("Access Denied: Card Already Used");
    }

    private boolean sendSessionStartTime() throws IOException {
        System.out.print("Enter Session Start Time: ");
        String sessionStartTime = userInput.readLine();
        out.println(sessionStartTime);
        String response = in.readLine();
        System.out.println("Server response: " + response);
        return response.equals("Session Time Valid");
    }

    private void receiveFinalResponse() throws IOException {
        String response = in.readLine();
        System.out.println("Server response: " + response);
    }

    private void close() {
        try {
            if (out != null) out.close();
            if (in != null) in.close();
            if (userInput != null) userInput.close();
            if (socket != null) socket.close();
        } catch (IOException e) {
            System.err.println("Error closing resources: " + e.getMessage());
        }
    }

    public static void main(String[] args) {
        try {
            Client client = new Client();
            client.start();
        } catch (UnknownHostException e) {
            System.err.println("Don't know about host " + HOST);
            System.exit(1);
        } catch (IOException e) {
            System.err.println("Couldn't get I/O for the connection to " + HOST);
            System.exit(1);
        }
    }
}