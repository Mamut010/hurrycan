# Hurry Can

A mini E-business project - server side

# Project setup instructions
- Download and install Docker.
- Clone the repository from https://github.com/Mamut010/hurrycan.git.
- Navigate to the project directory.
- Duplicate the .env.example file and rename it to .env.
- The .env file is ready to use. Modify the environment values in this file if customization is needed.
- Go to the secrets folder in the project root and duplicate the .password.example.txt file, renaming it to .password.txt.
- Open a terminal in the project root (ensure you're in the project root directory).
- Run the following command to install and run the server:
  `docker compose up --watch --build`
- For Windows users: Use the helper scripts in the scripts folder from the project root. Run the docker-reset script to initialize everything and start the server.
- Wait until you see the line below in the console:
  `php-server    | [Thu Nov 28 03:16:17.049050 2024] [core:notice] [pid 1:tid 1] AH00094: Command line: 'apache2 -D FOREGROUND'`
This indicates the server is ready.
- To seed the database, run the following command:
 `docker exec php-server /var/www/docker/migration.sh`
  Alternatively, on Windows, run the migration.cmd script.
- After seeing "Successfully seeding database," the setup is complete and the project is ready to go.
The development server is listening on http://localhost:9000 
