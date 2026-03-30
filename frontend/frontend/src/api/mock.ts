export const mockLogin = async (credentials: any) => {
  if (credentials.email === "test@test.com") {
    return {
      token: "fake-jwt-token",
      user: {
        id: 1,
        email: "test@test.com",
        roles: ["ROLE_USER"]
      }
    };
  }

  throw new Error("Invalid credentials");
};

export const mockRegister = async (data: any) => {
  return {
    message: "User created"
  };
};