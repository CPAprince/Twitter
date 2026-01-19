document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registration-form");
  const alerts = document.getElementById("alert-placeholder");

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const data = Object.fromEntries(new FormData(form));

    try {
      const user = await Api.post(window.routes.createUser, {
        email: data.email,
        password: data.password,
      });

      const auth = await Api.post(window.routes.login, {
        email: data.email,
        password: data.password,
      });

      // Handle both token and accessToken response formats
      const accessToken = auth.token || auth.accessToken;
      if (!accessToken) {
        throw new Error("Access token is missing.");
      }

      // Store token in localStorage for persistence
      if (window.Api && window.Api.setToken) {
        Api.setToken(accessToken);
      }

      await Api.post(
        window.routes.createProfile,
        { userId: user.id, name: data.name, bio: data.bio },
        accessToken
      );

      Alert.append(alerts, "You have been successfully registered!", "success");
      window.location.href = window.routes.successRedirect;
    } catch (e) {
      Alert.append(alerts, "Registration failed", "danger");
    }
  });
});
